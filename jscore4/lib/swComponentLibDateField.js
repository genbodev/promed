function getMinBirthDate(){
	var year_offset = 150,
		birthdate;

	if ( getGlobalOptions().date ) {
		birthdate = Ext.Date.parse( getGlobalOptions().date, 'd.m.Y' );
	} else {
		birthdate = new Date();
	}
	birthdate.setFullYear( ( birthdate.getFullYear() - year_offset ) );

	return Ext.Date.format(birthdate,'d.m.Y');
}
