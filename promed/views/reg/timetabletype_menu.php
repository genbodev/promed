[
<?php
	foreach($types as $type) {
?>
    {
        "id": "<?php echo $type->id ?>",
        "text": "<?php echo $type->name ?>",
        "group": 'settype',
        checked: false
    },
<?php
	}
?>
]

