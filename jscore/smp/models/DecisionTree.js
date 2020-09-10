/**
 * Модель дерева решений
 */
Ext6.define('smp.models.DecisionTree', {
	extend: 'Ext6.data.Model',
	//idProperty: 'AmbulanceDecigionTree_id',
	fields: [
		{
			name: 'AmbulanceDecigionTree_id',
			type: 'int'
		},
		{
			name: 'AmbulanceDecigionTree_nodeid',
			type: 'int'
		},
		{
			name: 'AmbulanceDecigionTree_nodepid',
			type: 'int'
		},
		{
			name: 'AmbulanceDecigionTree_Type',
			type: 'int' // 1 - вопрос, 2 - ответ
		}, 
		{
			name: 'AmbulanceDecigionTree_Text',
			type: 'string'
		},
		{
			name: 'CmpReason_id',
			type: 'int'
		},
		{
			name: 'leaf'
		}
	]
});
