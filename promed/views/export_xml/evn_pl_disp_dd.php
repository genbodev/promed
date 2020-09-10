<children>
	{child}
	<child>
		<idInternal>{idInternalChild}</idInternal>
		<idType>{idTypeChild}</idType>
		<name>
			<last>{lastName}</last>
			<first>{firstName}</first>
			<middle>{middleName}</middle>
		</name>
		<idSex>{idSex}</idSex>
		<dateOfBirth>{dateOfBirth}</dateOfBirth>
		<idCategory>{idCategory}</idCategory>
		<idDocument>{idDocument}</idDocument>
		<documentSer>{documentSer}</documentSer>
		<documentNum>{documentNum}</documentNum>
		<snils>{snils}</snils>
		<idPolisType>{idPolisType}</idPolisType>
		<polisSer>{polisSer}</polisSer>
		<polisNum>{polisNum}</polisNum>
		<idInsuranceCompany>{idInsuranceCompany}</idInsuranceCompany>
		<medSanName>{medSanName}</medSanName>
		<medSanAddress>{medSanAddress}</medSanAddress>
		<address>
			<fiasAoid>{fiasAoid}</fiasAoid>
			<cityName>{cityName}</cityName>
			<regionCode>{regionCode}</regionCode>
		</address>

		<education>
			<kladrDistr>{kladrDistr}</kladrDistr>
			<idEducType>{idEducType}</idEducType>
			<educOrgName>{educOrgName}</educOrgName>
		</education>

		<idEducationOrg>{idEducationOrg}</idEducationOrg>
		<idOrphHabitation>{idOrphHabitation}</idOrphHabitation>
		<dateOrphHabitation>{dateOrphHabitation}</dateOrphHabitation>
		<idStacOrg>{idStacOrg}</idStacOrg>

		<cards>
			{card}
			<card>
				<idInternal>{idInternalCard}</idInternal>
				<dateOfObsled>{dateOfObsled}</dateOfObsled>
				<ageObsled>{ageObsled}</ageObsled>
				<idType>{idTypeCard}</idType>

				<height>{height}</height>
				<weight>{weight}</weight>
				<headSize>{headSize}</headSize>
				<healthProblems>
					{healthProblems}
					<problem>{healthProblem}</problem>
					{/healthProblems}
				</healthProblems>
				<pshycDevelopment>
					<poznav>{poznav}</poznav>
					<motor>{motor}</motor>
					<emot>{emot}</emot>
					<rech>{rech}</rech>
				</pshycDevelopment>
				<pshycState>
					<psihmot>{psihmot}</psihmot>
					<intel>{intel}</intel>
					<emotveg>{emotveg}</emotveg>
				</pshycState>
				{sexFormulaMale}
				<sexFormulaMale>
					<P>{M_P}</P>
					<Ax>{M_Ax}</Ax>
					<Fa>{M_Fa}</Fa>
				</sexFormulaMale>
				{/sexFormulaMale}
				{sexFormulaFemale}
				<sexFormulaFemale>
					<P>{F_P}</P>
					<Ma>{F_Ma}</Ma>
					<Ax>{F_Ax}</Ax>
					<Me>{F_Me}</Me>
				</sexFormulaFemale>
				{/sexFormulaFemale}
				{menses}
				<menses>
					<menarhe>{menarhe}</menarhe>
					<characters>
						{chars}
						<char>{charValue}</char>
						{/chars}
					</characters>
				</menses>
				{/menses}

				<healthGroupBefore>{healthGroupBefore}</healthGroupBefore>
				<fizkultGroupBefore>{fizkultGroupBefore}</fizkultGroupBefore>

				<diagnosisBefore>
					{diagnosisBefore}
					<diagnosis>
						<mkb>{diagBeforeMKB}</mkb>
						<dispNablud>{diagBeforeDispNablud}</dispNablud>
						<lechen>
							{diagBeforeLechen}
							<condition>{diagBeforeConditionLechen}</condition>
							<organ>{diagBeforeOrganLechen}</organ>
							<notDone>
								<reason>{diagBeforeReasonLechen}</reason>
								<reasonOther>{diagBeforeReasonOtherLechen}</reasonOther>
							</notDone>
							{/diagBeforeLechen}
						</lechen>
						<reabil>
							{diagBeforeReabil}
							<condition>{diagBeforeConditionReab}</condition>
							<organ>{diagBeforeOrganReab}</organ>
							<notDone>
								<reason>{diagBeforeReasonReab}</reason>
								<reasonOther>{diagBeforeReasonOtherReab}</reasonOther>
							</notDone>
							{/diagBeforeReabil}
						</reabil>
						<vmp>{diagBeforeVMP}</vmp>
					</diagnosis>
					{/diagnosisBefore}
				</diagnosisBefore>

				<healthyMKB>{healthyMKB}</healthyMKB>

				<diagnosisAfter>
					{diagnosisAfter}
					<diagnosis>
						<mkb>{diagAfterMKB}</mkb>
						<firstTime>{firstTime}</firstTime>
						<dispNablud>{dispNablud}</dispNablud>
						<lechen>
							{diagAterLechen}
							<condition>{conditionLechen}</condition>
							<organ>{organLechen}</organ>
							{/diagAterLechen}
						</lechen>
						<reabil>
							{diagAfterReabil}
							<condition>{conditionReab}</condition>
							<organ>{organReab}</organ>
							{/diagAfterReabil}
						</reabil>
						<consul>
							{diagAfterConsul}
							<condition>{conditionConsul}</condition>
							<organ>{organConsul}</organ>
							<state>{stateConsul}</state>
							{/diagAfterConsul}
						</consul>
						<needVMP>{needVMP}</needVMP>
						<needSMP>{needSMP}</needSMP>
						<needSKL>{needSKL}</needSKL>
						<recommendNext>{recommendNext}</recommendNext>
					</diagnosis>
					{/diagnosisAfter}
				</diagnosisAfter>

				<invalid>
					{invalid}
					<type>{typeInvalid}</type>
					<dateFirstDetected>{dateFirstDetected}</dateFirstDetected>
					<dateLastConfirmed>{dateLastConfirmed}</dateLastConfirmed>
					<illnesses>
						{illnesses}
						<illness>{illnessValue}</illness>
						{/illnesses}
					</illnesses>
					<defects>
						{defects}
						<defect>{defectValue}</defect>
						{/defects}
					</defects>
					{/invalid}
				</invalid>

				<issled>
					<basic>
						{issledBasic}
						<record>
							<id>{basicIssledId}</id>
							<date>{basicIssledDate}</date>
							<result>{basicIssledResult}</result>
						</record>
						{/issledBasic}
					</basic>
					<other>
						{issledOther}
						<record>
							<date>{otherIssledDate}</date>
							<name>{otherIssledName}</name>
							<result>{otherIssledResult}</result>
						</record>
						{/issledOther}
					</other>
				</issled>

				<healthGroup>{healthGroup}</healthGroup>
				<fizkultGroup>{fizkultGroup}</fizkultGroup>
				<zakluchDate>{zakluchDate}</zakluchDate>
				{zakluchVrachName}
				<zakluchVrachName>
					<last>{lastNameMP}</last>
					<first>{firstNameMP}</first>
					<middle>{middleNameMP}</middle>
				</zakluchVrachName>
				{/zakluchVrachName}
				<osmotri>
					{osmotri}
					<record>
						<id>{osmotrId}</id>
						<date>{osmotrDate}</date>
					</record>
					{/osmotri}
				</osmotri>
				<recommendZOZH>{recommendZOZH}</recommendZOZH>

				<reabilitation>
					<date>{dateReab}</date>
					<state>{stateReab}</state>
				</reabilitation>

				<privivki>
					<state>{statePriv}</state>
					<privs>
						<priv></priv>
					</privs>
				</privivki>

				<oms>{oms}</oms>
			</card>
			{/card}
		</cards>
		<without_snils_reason>{without_snils_reason}</without_snils_reason>
		<without_snils_other>{without_snils_other}</without_snils_other>
	</child>
	{/child}
</children>