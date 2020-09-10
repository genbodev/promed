<!---/*NO PARSE JSON*/--->
<style>
	.symptoms {padding-left: 5px; margin: 4px;}
</style>
<?php 
	function printSymptomRecursive($symptom, $parent) {
		if ($symptom['type'] == 'radio' ) {
			if ($parent['type'] == 'check') {
				$disabled = "disabled=disabled";
			} else {
				$disabled = "";
			}
			echo "
			<li class='symptoms ".$symptom['visittype']."'>
				<input type='radio' id='symptom_{$symptom['id']}' name='symptoms[{$symptom['pid']}]' rel='symptom_{$symptom['pid']}' value='{$symptom['id']}' class='checkBox' {$disabled}>
				<label class='checkBoxLabel inactive' for='symptom_{$symptom['id']}' rel='symptom_{$symptom['pid']}'>{$symptom['name']}</label>
			</li>";
		} else if ($symptom['type'] == 'check' ) {
			echo "
			<li class='symptoms ".$symptom['visittype']."'>
				<input type='checkbox' id='symptom_{$symptom['id']}' name='symptoms[{$symptom['id']}]' value='{$symptom['id']}' class='checkBox' onchange=\"$('[rel=\'symptom_{$symptom['id']}\']').attr('disabled', !this.checked); if (!this.checked) $('[rel=\'symptom_{$symptom['id']}\']').attr('checked', false)\">
				<label class='checkBoxLabel inactive' for='symptom_{$symptom['id']}'>{$symptom['name']}</label>
			</li>";
		}
		if (isset($symptom['children'])) {
			echo "<ul class='symptoms ".$symptom['visittype']."'>";
			foreach ($symptom['children'] as $symptom_child) {
				printSymptomRecursive($symptom_child, $symptom);
			}
			echo "</ul>";
		}
	}
	
	foreach ($symptoms as $symptom_group) {
		echo "<b class='".$symptom_group['visittype']."'>{$symptom_group['name']}</b>";
		echo "<ul class='symptoms ".$symptom_group['visittype']."'>";
		foreach ($symptom_group['children'] as $symptom) {
			printSymptomRecursive($symptom, $symptom_group);
		}
		echo '</ul>';
	} 
?>