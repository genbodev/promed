<packet>
	<database_info>
		<region>{region}</region>
		<region_name>{region_name}</region_name>
		<system>{system}</system>
		<modulename>{modulename}</modulename>
		<haspd>{haspd}</haspd>
		<usehash>{usehash}</usehash>
	</database_info>

	<patients>
		{patient}
		<patient>
			<id {patient_id} />
			<data {patient_data} />

			{diag}
			<ds>
				<nodeid {diag_id} />
				<data {diag_data} />

				{oper}
				<oper>
					<nodeid {oper_id} />
					<data {oper_data} />
				</oper>
				{/oper}

				{ray}
				<ray>
					<nodeid {ray_id} />
					<data {ray_data} />
				</ray>
				{/ray}

				{chem}
				<chem>
					<nodeid {chem_id} />
					<data {chem_data} />
					{prep}
					<prep>
						<nodeid {prep_id} />
						<data {prep_data} />
					</prep>
					{/prep}
				</chem>
				{/chem}

				{horm}
				<horm>
					<nodeid {horm_id} />
					<data {horm_data} />
					{prep}
					<prep>
						<nodeid {prep_id} />
						<data {prep_data} />
					</prep>
					{/prep}
				</horm>
				{/horm}

			</ds>
			{/diag}

			{obs}
			<obs>
				<nodeid {obs_id} />
				<data {obs_data} />

				{obsds}
				<obsds>
					<nodeid {obsds_id} />
					<data {obsds_data} />
				</obsds>
				{/obsds}
			</obs>
			{/obs}

			{hosp}
			<hosp>
				<nodeid {hosp_id} />
				<data {hosp_data} />
			</hosp>
			{/hosp}

		</patient>
		{/patient}
	</patients>
</packet>