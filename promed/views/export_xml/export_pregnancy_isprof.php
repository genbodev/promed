<ResponseChangesMessage Version="{Version}" Uid="{Uid}" Created="{Created}" SystemCode="{SystemCode}" RequestId="{RequestId}" ChangesCount="{ChangesCount}" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	{Child}
	<Child Id="{Person_id}" UpdatedDate="{UpdatedDate}">
		<FirstName>{FirstName}</FirstName>
		<LastName>{LastName}</LastName>
		<MiddleName>{MiddleName}</MiddleName>
		<BirthDate>{BirthDate}</BirthDate>
		<Gender>{Gender}</Gender>
		<AddressName>{AddressName}</AddressName>
		<AddressOkato>{AddressOkato}</AddressOkato>
		<DocSeries>{DocSeries}</DocSeries>
		<DocNumber>{DocNumber}</DocNumber>
		<DocDate>{DocDate}</DocDate>
	</Child>
	{/Child}
	<IndicatorList>
		{IndicatorList}
		<Indicator UpdatedDate="{UpdatedDate}">
			<KindId>{KindId}</KindId>
			<StartDate>{StartDate}</StartDate>
			<Employee Id="{EmployeeId}" UpdatedDate="{EmployeeUpdatedDate}">
				<FirstName>{FirstName}</FirstName>
				<LastName>{LastName}</LastName>
				<MiddleName>{MiddleName}</MiddleName>
				<Post>{Post}</Post>
			</Employee>
		</Indicator>
		{/IndicatorList}
	</IndicatorList>
</ResponseChangesMessage>