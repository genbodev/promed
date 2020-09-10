Ext.define('common.BSME.ForenComplex.ux.ArchiveEvnComplexView',{
	extend: 'common.BSME.DefaultWP.ux.ArchiveView',

	displayFields: [
		{ text: '№ п/п',  dataIndex: 'EvnForensic_Num' },
		{ text: 'Фамилия, имя, отчество назначившего экспертизу', dataIndex: 'Person_FIO' },
		{ text: 'Основание для проведения экспертизы', dataIndex: 'EvnForensicComplexResearch_Base' },
		{ text: 'Дата проведения экспертизы', dataIndex: 'ActVersionForensic_insDT' },
		{ text: 'Вид экспертизы', dataIndex: 'EvnForensicType_Name' },
		{ text: 'Результаты экспертизы', dataIndex: 'EvnForensicComplex_Result' },
		{ text: 'Номер «Заключения Эксперта» «Акта»', dataIndex: 'ActVersionForensic_Num' },
		{ text: 'Фамилия судебно-медицинского эксперта', dataIndex: 'Expert_Fin' },
		{ text: 'Получивший результат', dataIndex: 'Receiver' }
	],

	tpl: [
		'<tpl for=".">',
		'<div class="archiveEvnSubViewCell">',
		'<div class="archiveEvnSubViewCell_left archiveCol">',
		'<tpl if="EvnForensic_Num"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">№ п/п</span><span class="text">{EvnForensic_Num}</span></p></tpl>',
		'<tpl if="Person_FIO"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">ФИО назначившего экспертизу</span><span class="text">{Person_FIO}</span></p></tpl>',
		'</div>',
		'<div class="archiveEvnSubViewCell_center archiveCol">',
		'<tpl if="EvnForensicComplexResearch_Base"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">Основание для проведения экспертизы</span><span class="text">{EvnForensicComplexResearch_Base}</span></p></tpl>',
		'<tpl if="ActVersionForensic_insDT"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">Дата проведения экспертизы</span><span class="text">{ActVersionForensic_insDT}</span></p></tpl>',
		'<tpl if="EvnForensicType_Name"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">Вид экспертизы</span><span class="text">{EvnForensicType_Name}</span></p></tpl>',
		'</div>',
		'<div class="archiveEvnSubViewCell_right archiveCol">',
		'<tpl if="ActVersionForensic_Num"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">Номер «Заключения»/«Акта»</span><span class="text">{ActVersionForensic_Num}</span></p></tpl>',
		'<tpl if="Expert_Fin"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">ФИО судебно-медицинского эксперта</span><span class="text">{Expert_Fin}</span></p></tpl>',
		'<tpl if="Receiver"><p class="ArchiveEvnSubViewCell_GrayText"><span class="label">Результат получил</span><span class="text">{Receiver}</span></p></tpl>',
		'</div>',
		'</div>',
		'</tpl>',
	],

	extraParams: {

	},
	initComponent: function(){
		var me = this;

		me.printButton = {
			xtype: 'splitbutton',
			text: 'Печать',
			iconCls: 'print16',
			disabled: true,
			menu: {
				xtype: 'menu',
				items: [
					{
						xtype: 'menuitem',
						itemId: 'print_request',
						text: 'Печать журнала',
						handler: function () {
							/*var pattern = '',
								dateFrom = me.datePickerRange.isVisible()?Ext.Date.format(me.datePickerRange.dateFrom, 'd.m.Y'):'01.01.1900',
								dateTo = me.datePickerRange.isVisible()?Ext.Date.format(me.datePickerRange.dateTo, 'd.m.Y'):Ext.Date.format(new Date, 'd.m.Y'),

							//paramMedService
								params = '&paramMedService='+(getGlobalOptions().CurMedService_id || '')+'&paramBegDate='+dateFrom+'&paramEndDate='+dateTo;

							switch (me.extraParams.JournalType){
								case 'EvnForensicSubDir':{pattern = 'CME_EvnForensicSub_1.rptdesign'; break;}
								case 'EvnForensicSubInsp':{pattern = 'CME_EvnForensicSub_2.rptdesign'; break;}
								case 'EvnForensicSubDoc':{pattern = 'CME_EvnForensicSub_4.rptdesign'; break;}
								case 'EvnForensicSubOwn':{pattern = 'CME_EvnForensicSub_3.rptdesign'; break;}
							}
							printBirt({
								'Report_FileName': pattern,
								'Report_Params': params,
								'Report_Format': 'pdf'
							});*/
						}
					},
					{
						xtype: 'menuitem',
						itemId: 'print_act',
						text: 'Печать заключения',
						handler: function () {
							/*var forensicId = me.archiveDataView.getSelectionModel().getSelection()[0].data.EvnForensic_id,
								pattern = 'CME_EvnForensicSub_List.rptdesign';

							printBirt({
								'Report_FileName': pattern,
								'Report_Params': '&paramEvnForensicSub='+forensicId,
								'Report_Format': 'pdf'
							});*/
						}
					}
				],
				listeners: {
					show: function(с){
						if(me.archiveDataView.getSelectionModel().hasSelection()){с.down('menuitem[itemId=print_act]').enable();}
						else {с.down('menuitem[itemId=print_act]').disable();}
					}
				}
			},
			listeners: {
				click: function(){
					this.showMenu();
				}
			}
		};

		Ext.apply(me,{
			store: new Ext.data.Store({
				autoLoad: false,
				pageSize: 10,
				storeId: this.id+'RequestListDataviewStore',
				idProperty: 'EvnForensic_id',
				fields: [
					{name: 'EvnForensic_id', type: 'int'},
					{name: 'EvnForensic_Num', type: 'string'},
					{name: 'Person_FIO', type: 'string'},
					//{name: 'Sex_Name', type: 'string'},
					//{name: 'Person_BirthDay', type: 'string'},
					//{name: 'Post_Name', type: 'string'},
					//{name: 'Address_Nick', type: 'string'},
					//{name: 'Iniciator', type: 'string'},
					{name: 'EvnForensicComplexResearch_Base', type: 'string'},
					{name: 'ActVersionForensic_insDT', type: 'string'},
					//{name: 'EvnForensicSub_AccidentDT', type: 'string'},
					{name: 'EvnForensicType_Name', type: 'string'},
					{name: 'EvnForensicSub_Result', type: 'string'},
					{name: 'ActVersionForensic_Num', type: 'string'},
					{name: 'Expert_Fin', type: 'string'},
					{name: 'ActVersionForensic_Num', type: 'string'},
					{name: 'Receiver', type: 'string'},
				],
				proxy: {
					type: 'ajax',
					url: '/?c=BSME&m=getEvnForensicComplexArchive',
					extraParams: me.extraParams,
					reader: {
						type: 'json',
						successProperty: 'success',
						totalProperty: 'totalCount',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			})
		});
		me.callParent(arguments);
	}
})

