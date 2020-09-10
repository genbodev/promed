<div prescr-count="{count}" style="display: none;">{count}</div>
<h3 style="margin-bottom: 10px">Назначения</h3>
<ul class="prescr-list">
	{data}
	<li><strong>{title}</strong>
	<ul class="sw-editor-sub-list">
			{items}
			<li>{item}</li>
			{/items}
		</ul>
	</li>
	{/data}
</ul>
