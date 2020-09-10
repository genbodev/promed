/**
 * swIPRARegistryGridWindow - Таблица с данными пациентов ИПРА.
 */
sw.Promed.swIPRARegistryGridWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swIPRARegistryGridWindow',
	objectName: 'swIPRARegistryGridWindow',
	objectSrc: '/jscore/Forms/Admin/swIPRARegistryGridWindow.js',
	layout: 'border',
	buttonAlign: 'center',
	title: 'Результат импорта выписок ИПРА пациентов',
	maximized: true,
	//Псих. бюро
	crazyMCElist: {
		ufa: [11, 12, 13, 14, 16]
	},
	width: 1250,
	height: 700,
	fieldWidth: 40,
	closable: true,
	resizable: false,
	closeAction: 'hide',
	draggable: true,
	saveInRegisterIPRA: function(jsondata) {
		Ext.Ajax.request({
			url: '/?c=IPRARegister&m=saveInRegisterIPRA',
			params: {
				jsondata: Ext.util.JSON.encode(jsondata)
			},
			callback: function(options, success, response) {
				var res = Ext.util.JSON.decode(response.responseText);
			}
		});
	},
	initComponent: function() {
		var form = this;
		Ext.apply(this, {
			buttonAlign: 'right',
			buttons: [{
					hidden: false,
					handler: function() {

					},
					iconCls: 'save16',
					id: 'addIPRARegistry',
					text: 'Включить в регистр',
					disabled: true,
					handler: function() {
						var store = Ext.getCmp('IPRA_grid').getStore();
						var items = store.data.items;
						var data = [];
						var errors = [];

						for (var k in items) {
							if (typeof items[k] == 'object') {

								if (items[k].data.Person_id == ''
								|| items[k].data.IPRAData_isValid != 2
								|| items[k].data.IPRAData_LpuName == ''
								|| items[k].data.IPRAData_LpuName == null
								|| items[k].IPRAData_Number == '') {
									errors.push(items[k].data);
								} else {
									data.push(items[k].data);
								}
							}
						}

						var jsondata = {
							data: data,
							errors: errors
						}

						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							msg: 'В регистр ИПРА будет добавлено: <b>' + data.length + '</b> записи.<br/>ИПРА с ошибками : <b>' + errors.length + '</b> не будут включены в регистр',
							title: 'Импорт выписок ИПРА',
							fn: function(buttonId) {
								if (buttonId != 'yes') {
									return false;
								} else {
									form.saveInRegisterIPRA(jsondata);
									form.close();
								}
							}.createDelegate(this)
						});



					}
				},

				{
					hidden: false,
					handler: function() {
						form.close();
					},
					iconCls: 'close16',
					text: 'Закрыть'
				}
			],
			items: [
				new Ext.grid.GridPanel({
					id: 'IPRA_grid',
					count: 0,
					title: '',
					disabled: false,
					border: false,
					mode: 'local',
					autoload: false,
					autoLoadData: false,
					toolbar: false,
					region: 'center',
					columns: [
						//Псих
						{
							dataIndex: 'CRZ',
							header: 'crazy',
							width: 40,
							hidden: true,
							renderer: function(v, p, r) {

								return r.get('IPRAData_FGUMCEnumber').inlist(Ext.getCmp('swIPRARegistryGridWindow').crazyMCElist.ufa) ?
									'<span class="x-grid3-check-col-non-border-on">&nbsp;&nbsp;&nbsp;</span>' :
									'<span class="x-grid3-check-col-on-non-border-red">&nbsp;&nbsp;&nbsp;</span>';

							}
						},
						//Пациент прошёл идентификацию и определён по МО
						{
							dataIndex: 'ipra',
							header: 'ИПРА',
							width: 40,
							renderer: function(v, p, r) {

								if (r.get('IPRAData_LpuName') != '' &&
									r.get('IPRAData_LpuName') != null &&
									r.get('Person_id') > 0 &&
									r.get('IPRAData_isValid') == 2 &&
									r.get('IPRAData_Number') != '' &&
									r.get('IPRAData_Protocol') != '' &&
									r.get('IPRAData_ProtocolDate') != '' &&
									r.get('IPRAData_issueDate') != ''
								) {
									return '<span style="color:green;; font-style:italic; font-size:14px">V</span>';
								} else {
									return '<span style="color:red; font-style:italic; font-size:14px">X</span>';
								}
							}
						},
						//Идентификация пациента
						{
							dataIndex: 'pacient',
							header: 'ИД',
							width: 40,
							renderer: function(v, p, r) {

								return r.get('Person_id') > 0 ?
									'<span style="color:green; font-style:italic; font-size:14px">V</span>' :
									'<span style="color:red; font-style:italic; font-size:14px">X</span>';


							}
						},
						//МО определения инвалида
						{
							dataIndex: 'lpu',
							header: 'МО',
							width: 40,
							renderer: function(v, p, r) {


								return r.get('IPRAData_LpuName') != '' && r.get('IPRAData_LpuName') != null ?
									'<span style="color:green; font-style:italic; font-size:14px">V</span>' :
									'<span style="color:red; font-style:italic; font-size:14px">X</span>';

							}
						},

							//Проверка на наличие всех данных ИПРА пациента
						{
							dataIndex: 'IPRADataAllFields',
							header: 'Все данные',
							width: 40,
							renderer: function(v, p, r) {
									var IPRAdataIsValid = r.get('IPRAData_isValid') == 2;

									return IPRAdataIsValid ?
											'<span style="color:green; font-style:italic; font-size:14px">V</span>' :
											'<span style="color:red; font-style:italic; font-size:14px">X</span>';
							}
						}, {
							dataIndex: 'IPRAData_isValid',
							header: 'IPRAData_isValid',
							width: 190,
							hidden: true
						}, {
							dataIndex: 'IPRAData_DirectionLPU_id',
							header: 'IPRAData_DirectionLPU_id',
							width: 190,
							hidden: true
						}, {
							dataIndex: 'IPRAData_DirectionLPU_Name',
							header: 'МО Справочника',
							hidden : true,
							width: 190
						}, {
							dataIndex: 'IPRAData_LpuName',
							header: 'МО определения',
							width: 190
						}, {
							dataIndex: 'IPRAData_SNILS',
							header: 'СНИЛС',
							width: 90
						}, {
							dataIndex: 'IPRAData_PersonFIO',
							header: 'Фамилия Имя Отчество',
							width: 200
						}, {
							dataIndex: 'IPRAData_BirthDate',
							header: 'Дата рождения',
							width: 90,
							renderer: function(v, p, r) {

								return (r.get('IPRAData_BirthDate') != '' && r.get('IPRAData_BirthDate') != null && r.get('IPRAData_BirthDate').length <= 10)?
								Ext.util.Format.date(r.get('IPRAData_BirthDate'),'d.m.Y'):
								'';
							}
						}, {
							dataIndex: 'IPRAData_IPRAident',
							header: 'MSE ID',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Number',
							header: '№ ИПРА',
							width: 90
						}, {
							dataIndex: 'IPRAData_Protocol',
							header: '№ Протокола',
							width: 90
						}, {
							dataIndex: 'IPRAData_ProtocolDate',
							header: 'Дата протокола',
							width: 90
						}, {
							dataIndex: 'IPRAData_FGUMCEshort',
							header: 'Бюро МСЭ',
							width: 140
						}, {
							dataIndex: 'IPRAData_FGUMCE',
							header: 'Бюро МСЭ (полное название)'
						}, {
							dataIndex: 'IPRAData_FGUMCEnumber',
							header: 'Номер бюро МСЭ'
						}, {
							dataIndex: 'IPRAData_Behavior',
							header: 'IPRAData_Behavior',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Communicate',
							header: 'IPRAData_Communicate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Compensate',
							header: 'IPRAData_Compensate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Confirm',
							header: 'IPRAData_Confirm',
							hidden:true
						}, {
							dataIndex: 'IPRAData_RequiredHelp',
							header: 'IPRAData_RequiredHelp',
							hidden:true
						}, {
							dataIndex: 'IPRAData_EndDate',
							header: 'Срок ИПРА',
							hidden:true
						}, {
							dataIndex: 'Lpu_id',
							header: 'Lpu_id прикрепления',
							hidden:true
						}, {
							dataIndex: 'LpuAttachName',
							header: 'Lpu прикрепления',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Learn',
							header: 'IPRAData_Learn',
							hidden:true
						}, {
							dataIndex: 'IPRAData_MedRehab',
							header: 'IPRAData_MedRehab',
							hidden:true
						}, {
							dataIndex: 'IPRAData_MedRehab_begDate',
							header: 'IPRAData_MedRehab_begDate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_MedRehab_endDate',
							header: 'IPRAData_MedRehab_endDate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Move',
							header: 'IPRAData_Move',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Orientation',
							header: 'IPRAData_Orientation',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Orthotics',
							header: 'IPRAData_Orthotics',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Orthotics_begDate',
							header: 'IPRAData_Orthotics_begDate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Orthotics_endDate',
							header: 'IPRAData_Orthotics_endDate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_ReconstructSurg',
							header: 'IPRAData_ReconstructSurg',
							hidden:true
						}, {
							dataIndex: 'IPRAData_ReconstructSurg_begDate',
							header: 'IPRAData_ReconstructSurg_begDate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_ReconstructSurg_endDate',
							header: 'IPRAData_ReconstructSurg_endDate',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Restoration',
							header: 'IPRAData_Restoration',
							hidden:true
						}, {
							dataIndex: 'IPRAData_SelfService',
							header: 'IPRAData_SelfService',
							hidden:true
						}, {
							dataIndex: 'IPRAData_Work',
							header: 'IPRAData_Work',
							hidden:true
						}, {
							dataIndex: 'IPRAData_isFirst',
							header: 'IPRAData_isFirst',
							hidden:true
						}, {
							dataIndex: 'IPRAData_issueDate',
							header: 'Дата выдачи ИПРА',
							hidden:true
						}, {
							dataIndex: 'IPRAData_DevelopDate',
							header: 'Дата разработки ИПРА',
							hidden:true
						}, {
							dataIndex: 'Person_id',
							header: 'Person_id',
							hidden:true
						}, {
							dataIndex: 'Person_FirName',
							header: 'Person_FirName',
							hidden: true
						}, {
							dataIndex: 'Person_SecName',
							header: 'Person_SecName',
							hidden: true
						}, {
							dataIndex: 'Person_SurName',
							header: 'Person_surName',
							hidden: true
						}, {
							dataIndex: 'IPRAData_Document_Num',
							header: 'IPRAData_Document_Num',
							hidden: true
						}, {
							dataIndex: 'filename',
							header: 'Файл',
							width: 300
						},{
							dataIndex:  'IPRAData_PrimaryProfession',
							header:     'IPRAData_PrimaryProfession',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_PrimaryProfessionExp',
							header:     'IPRAData_PrimaryProfessionExp',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_Qualification',
							header:     'IPRAData_Qualification',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_CurrentJob',
							header:     'IPRAData_CurrentJob',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_NotWorkYears',
							header:     'IPRAData_NotWorkYears',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_EmploymentOrientationExists',
							header:     'IPRAData_EmploymentOrientationExists',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_IsRegisteredInEmploymentService',
							header:     'IPRAData_IsRegisteredInEmploymentService',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_DisabilityGroup',
							header:     'IPRAData_DisabilityGroup',
							hidden:     true
						},{
							dataIndex:  'IPRAData_DisabilityCause',
							header:     'IPRAData_DisabilityCause',
							hidden:     true
						},{
							dataIndex:  'IPRAData_DisabilityCauseOther',
							header:     'IPRAData_DisabilityCauseOther',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_DisabilityGroupDate',
							header:     'IPRAData_DisabilityGroupDate',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_IsDisabilityGroupPrimary',
							header:     'IPRAData_IsDisabilityGroupPrimary',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_DisabilityEndDate',
							header:     'IPRAData_DisabilityEndDate',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_SentOrgOGRN',
							header:     'IPRAData_SentOrgOGRN',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_SentOrgName',
							header:     'IPRAData_SentOrgName',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_RehabPotential',
							header:     'IPRAData_RehabPotential',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_RehabPrognoz',
							header:     'IPRAData_RehabPrognoz',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_IsIntramural',
							header:     'IPRAData_IsIntramural',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_PrognozFuncRecovery',
							header:     'IPRAData_PrognozFuncRecovery',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_PrognozFuncCompensation',
							header:     'IPRAData_PrognozFuncCompensation',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_PrognozSelfService',
							header:     'IPRAData_PrognozSelfService',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_PrognozMoveIndependetly',
							header:     'IPRAData_PrognozMoveIndependetly',
							hidden:     true
						}, {
							dataIndex:  'IPRAData_PrognozOrientate',
							header:     'IPRAData_PrognozOrientate',
							hidden:     true
						},{
							dataIndex:  'IPRAData_PrognozCommunicate',
							header:     'IPRAData_PrognozCommunicate',
							hidden:     true
						},{
							dataIndex:  'IPRAData_PrognozBehaviorControl',
							header:     'IPRAData_PrognozBehaviorControl',
							hidden:     true
						},{
							dataIndex:  'IPRAData_PrognozLearning',
							header:     'IPRAData_PrognozLearning',
							hidden:     true
						},{
							dataIndex:  'IPRAData_PrognozWork',
							header:     'IPRAData_PrognozWork',
							hidden:     true
						},{
							dataIndex:  'IPRAData_Version',
							header:     'IPRAData_Version',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPerson_LastName',
							header:     'IPRAData_RepPerson_LastName',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPerson_FirstName',
							header:     'IPRAData_RepPerson_FirstName',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPerson_SecondName',
							header:     'IPRAData_RepPerson_SecondName',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonAD_Title',
							header:     'IPRAData_RepPersonAD_Title',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonAD_Series',
							header:     'IPRAData_RepPersonAD_Series',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonAD_Number',
							header:     'IPRAData_RepPersonAD_Number',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonAD_Issuer',
							header:     'IPRAData_RepPersonAD_Issuer',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonAD_IssueDate',
							header:     'IPRAData_RepPersonAD_IssueDate',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonID_Title',
							header:     'IPRAData_RepPersonID_Title',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonID_Series',
							header:     'IPRAData_RepPersonID_Series',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonID_Number',
							header:     'IPRAData_RepPersonID_Number',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonID_Issuer',
							header:     'IPRAData_RepPersonID_Issuer',
							hidden:     true
						},{
							dataIndex:  'IPRAData_RepPersonID_IssueDate',
							header:     'IPRAData_RepPersonID_IssueDate',
							hidden:      true
						},{
							dataIndex:  'IPRAData_RepPerson_SNILS',
							header:     'IPRAData_RepPerson_SNILS',
							hidden:     true
						}
					],
					store: new Ext.data.JsonStore({
						autoLoad: false,
						pageSize: 550,
						fields: [{
								name: 'IPRAData_IPRAident',
								type: 'string'
							}, {
								name: 'IPRAData_Behavior',
								type: 'string'
							}, {
								name: 'IPRAData_Communicate',
								type: 'string'
							}, {
								name: 'IPRAData_Compensate',
								type: 'string'
							}, {
								name: 'IPRAData_Confirm',
								type: 'string'
							},
							//Лпу направления, после сопряжения с РМИАС
							{
								name: 'IPRAData_DirectionLPU_Name',
								type: 'string'
							}, {
								name: 'IPRAData_DirectionLPU_id',
								type: 'string'
							},

							{
								name: 'IPRAData_EndDate',
								type: 'string'
							}, {
								name: 'IPRAData_FGUMCE',
								type: 'string'
							}, {
								name: 'IPRAData_FGUMCEshort',
								type: 'string'
							}, {
								name: 'IPRAData_FGUMCEnumber',
								type: 'string'
							}, {
								name: 'IPRAData_RecepientType',
								type: 'string'
							}, {
								name: 'IPRAData_Learn',
								type: 'string'
							}, {
								name: 'IPRAData_MedRehab',
								type: 'string'
							}, {
								name: 'IPRAData_MedRehab_begDate',
								type: 'string'
							}, {
								name: 'IPRAData_MedRehab_endDate',
								type: 'string'
							}, {
								name: 'IPRAData_Move',
								type: 'string'
							}, {
								name: 'IPRAData_Number',
								type: 'string'
							}, {
								name: 'IPRAData_Orientation',
								type: 'string'
							}, {
								name: 'IPRAData_Orthotics',
								type: 'string'
							}, {
								name: 'IPRAData_Orthotics_begDate',
								type: 'string'
							}, {
								name: 'IPRAData_Orthotics_endDate',
								type: 'string'
							}, {
								name: 'IPRAData_PersonFIO',
								type: 'string'
							}, {
								name: 'IPRAData_Protocol',
								type: 'string'
							}, {
								name: 'IPRAData_ProtocolDate',
								type: 'string'
							}, {
								name: 'IPRAData_ReconstructSurg',
								type: 'string'
							}, {
								name: 'IPRAData_ReconstructSurg_begDate',
								type: 'string'
							}, {
								name: 'IPRAData_ReconstructSurg_endDate',
								type: 'string'
							}, {
								name: 'IPRAData_Restoration',
								type: 'string'
							}, {
								name: 'IPRAData_SelfService',
								type: 'string'
							}, {
								name: 'IPRAData_Work',
								type: 'string'
							}, {
								name: 'IPRAData_isFirst',
								type: 'string'
							}, {
								name: 'IPRAData_issueDate',
								type: 'string'
							}, {
								name: 'Person_id',
								type: 'string'
							}, {
								name: 'IPRAData_LpuName',
								type: 'string'
							}, {
								name: 'IPRAData_BirthDate',
								type: 'string'
							}, {
								name: 'IPRAData_SNILS',
								type: 'string'
							}, {
								name: 'IPRAData_DevelopDate',
								type: 'string'
							},
							//Лпу прикрепления
							{
								name: 'Lpu_id',
								type: 'string'
							}, {
								name: 'LpuAttachName',
								type: 'string'
							},

							{
								name: 'Person_FirName',
								type: 'string'
							}, {
								name: 'Person_SecName',
								type: 'string'
							}, {
								name: 'Person_SurName',
								type: 'string'
							}, {
								name: 'IPRAData_Document_Num',
								type: 'string'
							}, {
								name: 'filename',
								type: 'string'
							},{
								name: 'IPRAData_PrimaryProfession',
								type: 'string'
							}, {
								name: 'IPRAData_PrimaryProfessionExp',
								type: 'string'
							}, {
								name: 'IPRAData_Qualification',
								type: 'string'
							}, {
								name: 'IPRAData_CurrentJob',
								type: 'string'
							}, {
								name: 'IPRAData_NotWorkYears',
								type: 'int'
							}, {
								name: 'IPRAData_EmploymentOrientationExists',
								type: 'string'
							}, {
								name: 'IPRAData_IsRegisteredInEmploymentService',
								type: 'string'
							}, {
								name: 'IPRAData_DisabilityGroup',
								type: 'int'
							}, {
								name: 'IPRAData_DisabilityCause',
								type: 'int'
							}, {
								name: 'IPRAData_DisabilityGroupDate',
								type: 'string'
							}, {
								name: 'IPRAData_IsDisabilityGroupPrimary',
								type: 'string'
							}, {
								name: 'IPRAData_DisabilityEndDate',
								type: 'string'
							}, {
								name: 'IPRAData_SentOrgOGRN',
								type: 'string'
							}, {
								name: 'IPRAData_SentOrgName',
								type: 'string'
							}, {
								name: 'IPRAData_RehabPotential',
								type: 'int'
							}, {
								name: 'IPRAData_RehabPrognoz',
								type: 'int'
							}, {
								name: 'IPRAData_IsIntramural',
								type: 'string'
							}, {
								name: 'IPRAData_PrognozFuncRecovery',
								type: 'int'
							}, {
								name: 'IPRAData_PrognozFuncCompensation',
								type: 'int'
							}, {
								name: 'IPRAData_PrognozSelfService',
								type: 'int'
							}, {
								name: 'IPRAData_PrognozMoveIndependetly',
								type: 'int'
							}, {
								name: 'IPRAData_PrognozOrientate',
								type: 'int'
							},{
								name: 'IPRAData_PrognozCommunicate',
								type: 'int'
							},{
								name: 'IPRAData_PrognozBehaviorControl',
								type: 'int'
							},{
								name: 'IPRAData_PrognozLearning',
								type: 'int'
							},{
								name: 'IPRAData_PrognozWork',
								type: 'int'
							},{
								name: 'IPRAData_Version',
								type: 'string'
							},{
								name: 'IPRAData_RepPerson_LastName',
								type: 'string'
							},{
								name: 'IPRAData_RepPerson_FirstName',
								type: 'string'
							},{
								name: 'IPRAData_RepPerson_SecondName',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonAD_Title',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonAD_Series',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonAD_Number',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonAD_Issuer',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonAD_IssueDate',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonID_Title',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonID_Series',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonID_Number',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonID_Issuer',
								type: 'string'
							},{
								name: 'IPRAData_RepPersonID_IssueDate',
								type: 'string'
							},{
								name: 'IPRAData_RepPerson_SNILS',
								type: 'string'
							},{
								name: 'IPRAData_DisabilityCauseOther',
								type: 'string'
							}
						],
						listeners: {
							load: function() {

								//Идентификация и орпеделение МО прикрепления
								var loadMask = new Ext.LoadMask(form.getEl(), {
									msg: "Подождите... Идентификация пациентов в РМИАС"
								});
								loadMask.show();

								var grid = Ext.getCmp('IPRA_grid');
								var store = grid.getStore().data.items;

								var countItems = grid.getStore().data.items.length;

								for (var k in store) {
									if (typeof store[k] == 'object') {
										var rec = store[k];

										var Person_FirName = rec.get('Person_FirName');
										var Person_SecName = rec.get('Person_SecName');
										var SNILS = rec.get('IPRAData_SNILS');
										var Document_Num = rec.get('IPRAData_Document_Num');
										var BirthDate = rec.get('IPRAData_BirthDate');
										var IPRAData_DirectionLPU_id = rec.get('IPRAData_DirectionLPU_id');

										var params = {
											idx: k,
											Person_FirName: Person_FirName,
											Person_SecName: Person_SecName,
											Person_Snils: SNILS,
											Document_Num: Document_Num,
											Person_BirthDate: BirthDate,
											IPRAData_DirectionLPU_id: IPRAData_DirectionLPU_id
										}
										Ext.getCmp('swIPRARegistryGridWindow').storeLength = grid.getStore().data.length;

										var paramsCheckIPRAdata = {
												idx: k,
												IPRARegistryData_SelfService : rec.get('IPRAData_SelfService'),
												IPRARegistryData_Move : rec.get('IPRAData_Move'),
												IPRARegistryData_Orientation : rec.get('IPRAData_Orientation'),
												IPRARegistryData_Communicate : rec.get('IPRAData_Communicate'),
												IPRARegistryData_Learn : rec.get('IPRAData_Learn'),
												IPRARegistryData_Work : rec.get('IPRAData_Work'),
												IPRARegistryData_Behavior : rec.get('IPRAData_Behavior'),
												IPRARegistry_isFirst : rec.get('IPRAData_isFirst')=='true' ? 2 : 1,
												IPRARegistry_issueDate : rec.get('IPRAData_issueDate'),
												IPRARegistry_FGUMCEnumber : rec.get('IPRAData_FGUMCEnumber'),
												IPRARegistry_Number : rec.get('IPRAData_Number'),
												IPRARegistry_Protocol : rec.get('IPRAData_Protocol'),
												IPRARegistry_ProtocolDate : rec.get('IPRAData_ProtocolDate'),
												IPRARegistry_DevelopDate : rec.get('IPRAData_DevelopDate'),
												IPRARegistryData_MedRehab : rec.get('IPRAData_MedRehab'),
												IPRARegistryData_MedRehab_begDate : rec.get('IPRAData_MedRehab_begDate'),
												IPRARegistryData_ReconstructSurg : rec.get('IPRAData_ReconstructSurg'),
												IPRARegistryData_ReconstructSurg_begDate : rec.get('IPRAData_ReconstructSurg_begDate'),
												IPRARegistryData_Orthotics : rec.get('IPRAData_Orthotics'),
												IPRARegistryData_Orthotics_begDate : rec.get('IPRAData_Orthotics_begDate'),
												IPRARegistryData_Restoration : rec.get('IPRAData_Restoration'),
												IPRARegistryData_Compensate : rec.get('IPRAData_Compensate'),
												IPRARegistryData_PrimaryProfession              : rec.get('IPRAData_PrimaryProfession'),
												IPRARegistryData_PrimaryProfessionExperience    : rec.get('IPRAData_PrimaryProfessionExp'),
												IPRARegistryData_Qualification                  : rec.get('IPRAData_Qualification'),
												IPRARegistryData_CurrentJob                     : rec.get('IPRAData_CurrentJob'),
												IPRARegistryData_NotWorkYears                   : rec.get('IPRAData_NotWorkYears'),
												IPRARegistryData_EmploymentOrientationExists    : rec.get('IPRAData_EmploymentOrientationExists'),
												IPRARegistryData_IsRegisteredInEmploymentService: rec.get('IPRAData_IsRegisteredInEmploymentService'),
												IPRARegistryData_DisabilityGroup                : rec.get('IPRAData_DisabilityGroup'),
												IPRARegistryData_DisabilityCause                : rec.get('IPRAData_DisabilityCause'),
												IPRARegistryData_DisabilityCauseOther           : rec.get('IPRAData_DisabilityCauseOther'),
												IPRARegistryData_DisabilityEndDate              : rec.get('IPRAData_DisabilityEndDate'),
												IPRARegistryData_DisabilityGroupDate            : rec.get('IPRAData_DisabilityGroupDate'),
												IPRARegistryData_IsDisabilityGroupPrimary       : rec.get('IPRAData_IsDisabilityGroupPrimary'),
												IPRARegistryData_RehabPotential                 : rec.get('IPRAData_RehabPotential'),
												IPRARegistryData_RehabPrognoz                   : rec.get('IPRAData_RehabPrognoz'),
												IPRARegistryData_IsIntramural                   : rec.get('IPRAData_IsIntramural'),
												IPRARegistryData_PrognozFuncRecovery            : rec.get('IPRAData_PrognozFuncRecovery'),
												IPRARegistryData_PrognozFuncCompensation        : rec.get('IPRAData_PrognozFuncCompensation'),
												IPRARegistryData_PrognozSelfService             : rec.get('IPRAData_PrognozSelfService'),
												IPRARegistryData_PrognozMoveIndependetly        : rec.get('IPRAData_PrognozMoveIndependetly'),
												IPRARegistryData_PrognozOrientate               : rec.get('IPRAData_PrognozOrientate'),
												IPRARegistryData_PrognozCommunicate             : rec.get('IPRAData_PrognozCommunicate'),
												IPRARegistryData_PrognozBehaviorControl         : rec.get('IPRAData_PrognozBehaviorControl'),
												IPRARegistryData_PrognozLearning                : rec.get('IPRAData_PrognozLearning'),
												IPRARegistryData_PrognozWork                    : rec.get('IPRAData_PrognozWork'),
												IPRARegistryData_RepPerson_LastName             : rec.get('IPRAData_RepPerson_LastName'),
												IPRARegistryData_RepPerson_FirstName            : rec.get('IPRAData_RepPerson_FirstName'),
												IPRARegistryData_RepPerson_SecondName           : rec.get('IPRAData_RepPerson_SecondName'),
												IPRARegistryData_RepPerson_SNILS                : rec.get('IPRAData_RepPerson_SNILS'),
												IPRARegistryData_RepPersonAD_Title              : rec.get('IPRAData_RepPersonAD_Title'),
												IPRARegistryData_RepPersonAD_Series             : rec.get('IPRAData_RepPersonAD_Series'),
												IPRARegistryData_RepPersonAD_Number             : rec.get('IPRAData_RepPersonAD_Number'),
												IPRARegistryData_RepPersonAD_Issuer             : rec.get('IPRAData_RepPersonAD_Issuer'),
												IPRARegistryData_RepPersonAD_IssueDate          : rec.get('IPRAData_RepPersonAD_IssueDate'),
												IPRARegistryData_RepPersonID_Title              : rec.get('IPRAData_RepPersonID_Title'),
												IPRARegistryData_RepPersonID_Series             : rec.get('IPRAData_RepPersonID_Series'),
												IPRARegistryData_RepPersonID_Number             : rec.get('IPRAData_RepPersonID_Number'),
												IPRARegistryData_RepPersonID_Issuer             : rec.get('IPRAData_RepPersonID_Issuer'),
												IPRARegistryData_RepPersonID_IssueDate          : rec.get('IPRAData_RepPersonID_IssueDate'),
												IPRARegistry_Version                            : rec.get('IPRAData_Version')
										}

										Ext.Ajax.request(
										{
												params: paramsCheckIPRAdata,
												url: '/?c=IPRARegister&m=checkIPRAdataIsValid',
												callback: function(options, success, response)
												{

														if (success)
														{
																var res = Ext.util.JSON.decode(response.responseText);
																var idx = res.idx;
																grid.getSelectionModel().selectRow(idx);
																var selected = grid.getSelectionModel().getSelected();
																if (res.isValid) {
																		selected.set('IPRAData_isValid', res.isValid);
																		selected.commit();
																}
														}
												}
										});

										Ext.Ajax.request({
											params: params,
											success: function(response, options) {
												if (typeof(Ext.getCmp('swIPRARegistryGridWindow')) == 'undefined') {
													return;
												}

												var res = Ext.util.JSON.decode(response.responseText);
												var i = grid.count++;


												grid.getSelectionModel().selectRow(res.idx);

												var selected = grid.getSelectionModel().getSelected();

												var idx = res.idx;

												if (idx != undefined) {
													var rowEl = grid.getView().getRow(idx);
													rowEl.scrollIntoView(grid.getGridEl(), false);
												}


												if (res.Person_id) {
													selected.set('Person_id', res.Person_id);
												}
												if (res.Lpu_id) {
													selected.set('Lpu_id', res.Lpu_id);
												}
												if (res.LpuAttachName) {
													selected.set('LpuAttachName', res.LpuAttachName);
												}
												//Если сопряжение по справочника МО есть - укажем Lpu_id и LpuNick для МО, направившей на МСЕ
												selected.set('IPRAData_DirectionLPU_id', res.LpuDirection_id);
												selected.set('IPRAData_DirectionLPU_Name', res.LpuDirection_Name);

												//псих.
												if (getRegionNick() == 'ufa' && selected.get('IPRAData_FGUMCEnumber').inlist(Ext.getCmp('swIPRARegistryGridWindow').crazyMCElist.ufa)) {
													//МО по направлению
														selected.set('IPRAData_LpuName', selected.get('IPRAData_DirectionLPU_Name'));
													//}
												} else {
													//если есть прикрепление - то туда
													if (res.LpuAttachName != '' && res.LpuAttachName != null) {
														selected.set('IPRAData_LpuName', res.LpuAttachName);
													}
													//иначе по направлению
													else {
														selected.set('IPRAData_LpuName', selected.get('IPRAData_DirectionLPU_Name'));

													}
												}

												selected.commit();

												loadMask.hide();

												if (grid.getStore().data.items.length == (i + 1)) {

													sw.swMsg.alert('Результат', 'Идентификация пациентов закончена!');
													Ext.getCmp('addIPRARegistry').setDisabled(false);

												}
											},
											callback : function(){

											},
											url: '?c=IPRARegister&m=getIdentityPacient',
											failure: function(response, options) {
												var response_obj = Ext.util.JSON.decode(response.responseText);

												loadMask.hide();

												if (grid.getStore().data.items.length == (i + 1)) {
													Ext.getCmp('addIPRARegistry').setDisabled(false);
												}
											}
										});
									}
								}
							}
						}
					}),
					actions: [{
						name: 'action_add',
						text: 'Создать',
						hidden: true
					}, {
						name: 'action_edit',
						text: 'Изменить',
						hidden: true
					}, {
						name: 'action_delete',
						text: 'Удалить',
						hidden: true
					}, {
						name: 'action_view',
						hidden: true
					}, {
						name: 'action_refresh',
						hidden: true
					}, {
						name: 'action_print',
						hidden: true
					}],


				})
			]
		});
		sw.Promed.swIPRARegistryGridWindow.superclass.initComponent.apply(this, arguments);
	},
	close: function() {
		this.hide();
		this.destroy();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];
	},
	show: function(params) {
		this.IPRAdata_decode = params.IPRAdata_decode;
		Ext.getCmp('IPRA_grid').getStore().removeAll();
		Ext.getCmp('IPRA_grid').getStore().loadData(Ext.getCmp('swIPRARegistryGridWindow').IPRAdata_decode);

		sw.Promed.swIPRARegistryGridWindow.superclass.show.apply(this, arguments);
	}

});