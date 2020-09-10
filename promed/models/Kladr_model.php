<?php
class Kladr_model extends CI_Model {
    /**
     * Kladr_model constructor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @return bool|mixed
     */
    function getJobsList(){
        $query = 'use msdb;select enabled,name,description from sysjobs with(nolock) order by name';
        $result = $this->db->query($query);
        sqlsrv_next_result($result->result_id);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function isJobRunning($data){
        $query = 'use msdb;select count(*) as count from sysjobactivity a with(nolock)
            join sysjobs b with(nolock) on a.job_id = b.job_id where b.name = :JobName
            and a.stop_execution_date is null and a.start_execution_date is not null';
        $result = $this->db->query($query,array('JobName' => $data['jobName']));
        sqlsrv_next_result($result->result_id);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function getStepsList($data){
        $query = 'use msdb;select sjs.step_id,
                    sjs.step_name
                    from sysjobsteps sjs with(nolock)
                    where sjs.job_id = (select job_id from sysjobs with(nolock) where name = :JobName)
                    order by step_id';
        $result = $this->db->query($query,array('JobName' => $data['jobName']));
        sqlsrv_next_result($result->result_id);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function startJob($data){
        $query = 'use msdb;exec sp_start_job
                    @job_name = :JobName,
                    @step_name = :StepName';
        $result = $this->db->query($query,
                array('JobName' => $data['jobName'],
                      'StepName' => $data['stepName']));
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function stopJob($data){
        $query = 'use msdb;exec sp_stop_job
                    @job_name = :JobName';
        $result = $this->db->query($query,
                array('JobName' => $data['jobName']));
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function getJobsRunning($data) {
        $query = "
			-- текущая выполняющаяся
			select	cast(4 as int) as run_status,
					'Выполняется...' as run_status_name,
					convert(varchar(19),sja.start_execution_date,120) as run_datetime,
					convert(varchar(19),sja.stop_execution_date,120) as stop_datetime
			from	msdb.dbo.sysjobactivity sja with(nolock)
			where	sja.job_id = (select	j.job_id
								  from		msdb.dbo.sysjobs j with(nolock)
								  where		j.name = 'KLADR_Update')
					and (sja.start_execution_date is not null and sja.stop_execution_date is null)
			union all
			-- история запусков
            select	jhb.run_status,
                        case jhb.run_status
                            when 0 then 'Ошибка'
                            when 1 then 'Ок'
                            when 2 then 'Повтор'
                            when 3 then 'Отмена'
                            when 4 then 'Выполняется...'
                            when 5 then 'Неизвестно'
                        end as run_status_name,
                        (CONVERT(varchar(10), (SUBSTRING(cast(jhb.run_date as varchar(8)), 1, 4) + '-' +
                            SUBSTRING(cast(jhb.run_date as varchar(8)), 5, 2) + '-' +
                            SUBSTRING(cast(jhb.run_date as varchar(8)), 7, 2)), 104) + ' ' +
                            CONVERT(varchar(8), (Substring(cast((replicate('0', 6 -
                            len(cast(jhb.run_time as varchar(6)) )) +
                            cast(jhb.run_time as varchar(6))) as varchar(8)), 1, 2) + ':' +
                            Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) +
                            cast(jhb.run_time as varchar(6))) as varchar(8)), 3, 2) + ':' +
                            Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) +
                            cast(jhb.run_time as varchar(6))) as varchar(8)), 5, 2)), 114)) as run_datetime,
                        CONVERT(varchar(19),DATEADD(hour,
                            CAST(Substring(cast((replicate('0', 6 - len(cast(jhb.run_duration as varchar(6)) )) +
				cast(jhb.run_duration as varchar(6))) as varchar(8)), 1, 2) as int),
                            DATEADD(minute,
				cast(Substring(cast((replicate('0', 6 - len(cast(jhb.run_duration as varchar(6)) )) +
				cast(jhb.run_duration as varchar(6))) as varchar(8)), 3, 2) as int),
                            DATEADD(second,
				cast(Substring(cast((replicate('0', 6 - len(cast(jhb.run_duration as varchar(6)) )) +
                                cast(jhb.run_duration as varchar(6))) as varchar(8)), 5, 2) as int),
				-- к чему прибавляем (дата начала)
				SUBSTRING(cast(jhb.run_date as varchar(8)), 1, 4) + '-' +
				SUBSTRING(cast(jhb.run_date as varchar(8)), 5, 2) + '-' +
				SUBSTRING(cast(jhb.run_date as varchar(8)), 7, 2) + ' ' +
                                    Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) +
					cast(jhb.run_time as varchar(6))) as varchar(8)), 1, 2) + ':' +
                                    Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) +
                                        cast(jhb.run_time as varchar(6))) as varchar(8)), 3, 2) + ':' +
                                    Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) +
                                        cast(jhb.run_time as varchar(6))) as varchar(8)), 5, 2)))),120) as stop_datetime
            from	msdb.dbo.sysjobhistory jhb with(nolock)
            where	jhb.job_id = (select	j.job_id
                                      from	msdb.dbo.sysjobs j with(nolock)
                                      where	j.name = :JobName)
                        and jhb.step_id = 0
            order by 3 desc";
        $result = $this->db->query($query, array('JobName' => $data['jobName']));

        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
    function getHistoryByInterval($data) {
        $query = "
                declare @stopDate datetime = dbo.tzGetDate();
                if :Stop_DT is not null
                    set @stopDate = :Stop_DT
                select	jhb.step_name,
                        jhb.message,
                        jhb.run_status,
                        jhb.step_id,
                        case jhb.run_status
                            when 0 then 'ошибка'
                            when 1 then 'ок'
                            when 2 then 'повтор'
                            when 3 then 'отмена'
                            when 4 then 'выполняется...'
                            when 5 then 'неизвестно'
                        end as run_status_name,
                        (CONVERT(varchar(10), (SUBSTRING(cast(jhb.run_date as varchar(8)), 1, 4) + '-' +
                            SUBSTRING(cast(jhb.run_date as varchar(8)), 5, 2) + '-' +
                            SUBSTRING(cast(jhb.run_date as varchar(8)), 7, 2)), 104) + ' ' +
                            CONVERT(varchar(8), (Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) + cast(jhb.run_time as varchar(6))) as varchar(8)), 1, 2) + ':' +
                            Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) + cast(jhb.run_time as varchar(6))) as varchar(8)), 3, 2) + ':' +
                            Substring(cast((replicate('0', 6 - len(cast(jhb.run_time as varchar(6)) )) + cast(jhb.run_time as varchar(6))) as varchar(8)), 5, 2)), 114)) as run_datetime
		from	msdb.dbo.sysjobhistory jhb with(nolock)
                where	jhb.job_id = (select	j.job_id
                                      from	msdb.dbo.sysjobs j with(nolock)
                                      where	j.name = :JobName)
                        and jhb.step_id <> 0 -- итог в 'дереве'
			and(cast((CONVERT(varchar(10), (SUBSTRING(cast(jhb.run_date as varchar(8)), 1, 4) + '-' +
			       SUBSTRING(cast(jhb.run_date as varchar(8)), 5, 2) + '-' +
			       SUBSTRING(cast(jhb.run_date as varchar(8)), 7, 2)), 104) + ' ' +
                            CONVERT(varchar(8), (Substring(cast((replicate('0', 6 -
				len(cast(jhb.run_time as varchar(6)) )) +
				cast(jhb.run_time as varchar(6))) as varchar(8)), 1, 2) + ':' +
			       Substring(cast((replicate('0', 6 -
			        len(cast(jhb.run_time as varchar(6)) )) +
			        cast(jhb.run_time as varchar(6))) as varchar(8)), 3, 2) + ':' +
			       Substring(cast((replicate('0', 6 -
			        len(cast(jhb.run_time as varchar(6)) )) +
			        cast(jhb.run_time as varchar(6))) as varchar(8)), 5, 2)), 114)) as datetime)
			between :Start_DT and @stopDate)
		order by	jhb.step_id asc,
                        	jhb.run_date,
                                jhb.run_time
		";
        $result = $this->db->query($query, array('Start_DT' => $data['Start_DT'],
                'Stop_DT' => $data['Stop_DT'],'JobName'=>$data['jobName']));

        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
}
