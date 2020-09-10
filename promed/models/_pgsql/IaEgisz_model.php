<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * IaEgisz_model - Модель для авторизации через ИА ЕГИСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      24.12.2018
 */
class IaEgisz_model extends SwPgModel
{
	var $IaEgisz_config = array(); // конфиг

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->library('textlog', array('file' => 'iaegisz_' . date('Y-m-d') . '.log'));
		$this->config->load('esia');
		$this->esia_config = $this->config->item('esia');
	}

	/**
	 * Логин через ИА ЕГИСЗ
	 */
	function login($data)
	{
		$this->textlog->add('Попытка входа через esia');
		$guid = GUID();
		$egisz_path = $this->esia_config['egisz_path'];
		$client_id = $this->esia_config['client_id'];
		$redirect_uri = $this->esia_config['redirect_uri'];

		// пример ответа от ИА ЕГИСЗ
		// $_SESSION['egisz_guid'] = '4FE3E112-836B-8E1C-71A7-8C8CB8AC0AB1';
		// $data['SAMLResponse'] = 'PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphc3NlcnRpb24iIERlc3RpbmF0aW9uPSJodHRwczovL3BybS5wcm9tZWR3ZWIucnUvP2M9SWFFZ2lzeiZhbXA7bT1sb2dpbiIgSUQ9IklEXzdhYzZmMDdkLTlhZmMtNGJlZi05MmE3LTQxMGVlN2E2MzBlYSIgSW5SZXNwb25zZVRvPSI0RkUzRTExMi04MzZCLThFMUMtNzFBNy04QzhDQjhBQzBBQjEiIElzc3VlSW5zdGFudD0iMjAxOC0xMi0yOFQxNjoxNTowOS43MTdaIiBWZXJzaW9uPSIyLjAiPjxzYW1sOklzc3Vlcj5odHRwczovL2lhLXRlc3QuZWdpc3oucm9zbWluemRyYXYucnUvcmVhbG1zL21hc3Rlcjwvc2FtbDpJc3N1ZXI+PGRzOlNpZ25hdHVyZSB4bWxuczpkcz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PGRzOlNpZ25lZEluZm8+PGRzOkNhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIi8+PGRzOlJlZmVyZW5jZSBVUkk9IiNJRF83YWM2ZjA3ZC05YWZjLTRiZWYtOTJhNy00MTBlZTdhNjMwZWEiPjxkczpUcmFuc2Zvcm1zPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSIvPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiPjxkczpJbmNsdXNpdmVOYW1lc3BhY2VzIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiIFByZWZpeExpc3Q9InhzIi8+PC9kczpUcmFuc2Zvcm0+PC9kczpUcmFuc2Zvcm1zPjxkczpEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSIvPjxkczpEaWdlc3RWYWx1ZT5MUVMvRXJqcExkWE53S3FxTjI5NTFKL1FQcDQ9PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPlY1ZWVKYzZMOFNLd0EvQWFkN1J0bXJxQmNhTXQ5aStlYzlySjY5aUFXek1ja1dyY0FWR0lYWEpRT2Y4eHo3bGpxbjJzRGtTRlAwYnVWa3krOEVFK25lQW1LRFpJbk5GVVpsSUlPVzN0NmFKYVU5SlRJK2U0dFNZNmhxdnhjTUVONElZbTVqZkMzL2ptQnFHSC9zY3kyeUxhZUFCanUwSndDamhsd3VIakVUV0FEbXBQOHAwYm9iTDJ1bitHdnBtY2Z2VGZsbElvT2lXMk0rWDRLYnRSajhUcXlNa1NaM0swMEtGQWJvdjAvWC9zSkNXTm9Zamt1dUhTRjVkYUxYR25ndHJpemVCU0lDT3d6TXM2a1FiVlZLdUsrRFFNVVdnc0xQQVlnQ0g2OHVNaEdJRlNyVkRXWjVPTDFwNjJOb096SG9vZkdHS1R2MFJzK1pLZXlVQ0h3dz09PC9kczpTaWduYXR1cmVWYWx1ZT48ZHM6S2V5SW5mbz48ZHM6WDUwOURhdGE+PGRzOlg1MDlDZXJ0aWZpY2F0ZT5NSUlDbXpDQ0FZTUNCZ0ZTUHpqMGJUQU5CZ2txaGtpRzl3MEJBUXNGQURBUk1ROHdEUVlEVlFRRERBWnRZWE4wWlhJd0hoY05NVFl3TVRFME1EZ3hPVEF6V2hjTk1qWXdNVEUwTURneU1EUXpXakFSTVE4d0RRWURWUVFEREFadFlYTjBaWEl3Z2dFaU1BMEdDU3FHU0liM0RRRUJBUVVBQTRJQkR3QXdnZ0VLQW9JQkFRRElsMWVvbnlWVHlxVk0vUis0KzZSRFBObGxPTGFFUHFOUkMwcFBXREVtQUFUdXpzdUREakhhRXpBcmJYc2VqMzBZaytqMWg2alZoM3BMdGZ3WUp5WW5OOTI2WmNRdFNKcFVITitpRWNOcUlCK2QzTkhlKzRsSFBMdmI4VTE5WTRUc25kY1dUajl0RzFLcEdET1pydEdCdTNUWXRTR09HYWVMZEVCNVZsK0FwT2lLdGxIZU13c0V3ZENyNVlLYzIyS1RRcGljVkRESE1PaVE3K0IxRWplN28rd3ZDa1FGcGM2dDEwM0xlemhkdDBhelpIOFY4REo1TnNjVlJ4SGN2L2k3alpJRnJZLzd1MUJtaFBmc0lqangyR3RDL0JFYUcrTU53OWw4YTRKc1RvUDJzQ1NBNkxOR2grQm5ncC9ubEF0SDl6SXo0eFdzU3NRUTJQK0NJNmZQQWdNQkFBRXdEUVlKS29aSWh2Y05BUUVMQlFBRGdnRUJBRjRZQ2d5L0F2a1RPMTFLdFRqQ2Nnb3lDbk1TVUZRcW5nbHdOVkp2YjFuMVdZR3FrYkF0KzFabDhHWmsyOEhGYmRBeU96SXAwUVhHS3NYVEFQUkhjR2JsT1Y1ZmFNTTgvWW5yR0RkSDQ3SFNrUld1QnlrWmdFd1c5d1o2R3FqMmVMVi9OTnYwMzBBaUpyaUVXTFh0ZEFRRGZmRmJCbXJKM2JHanlPamxLa2tFeHo3bXJGU1JyMmVMWHdDL1BUMzY0OFJONmF5ODM3K1E5WkxsVUExZVdmY2JzeFJ6Z256WUhtNCtpQzZEN3VsTTNSbGhZN1dvZW0rUlFFOSs3bkEzbTRscnEwZGY0dTNZYzEwU0NRMmJWOTdrQ0FXdmxidGZzdW8ySlBlVEZEaGRoSzhBTFVaWXd6MWMvbWNMQTIvK2JkaU1FUVh3MnllT0NqUzhvRDh6Qk53PTwvZHM6WDUwOUNlcnRpZmljYXRlPjwvZHM6WDUwOURhdGE+PGRzOktleVZhbHVlPjxkczpSU0FLZXlWYWx1ZT48ZHM6TW9kdWx1cz55SmRYcUo4bFU4cWxUUDBmdVB1a1F6elpaVGkyaEQ2alVRdEtUMWd4SmdBRTdzN0xndzR4MmhNd0syMTdIbzk5R0pQbzlZZW8xWWQ2UzdYOEdDY21KemZkdW1YRUxVaWFWQnpmb2hIRGFpQWZuZHpSM3Z1SlJ6eTcyL0ZOZldPRTdKM1hGazQvYlJ0U3FSZ3ptYTdSZ2J0MDJMVWhqaG1uaTNSQWVWWmZnS1RvaXJaUjNqTUxCTUhRcStXQ25OdGlrMEtZbkZRd3h6RG9rTy9nZFJJM3U2UHNMd3BFQmFYT3JkZE55M3M0WGJkR3MyUi9GZkF5ZVRiSEZVY1IzTC80dTQyU0JhMlArN3RRWm9UMzdDSTQ4ZGhyUXZ3UkdodmpEY1BaZkd1Q2JFNkQ5ckFrZ09pelJvZmdaNEtmNTVRTFIvY3lNK01WckVyRUVOai9naU9uenc9PTwvZHM6TW9kdWx1cz48ZHM6RXhwb25lbnQ+QVFBQjwvZHM6RXhwb25lbnQ+PC9kczpSU0FLZXlWYWx1ZT48L2RzOktleVZhbHVlPjwvZHM6S2V5SW5mbz48L2RzOlNpZ25hdHVyZT48c2FtbHA6U3RhdHVzPjxzYW1scDpTdGF0dXNDb2RlIFZhbHVlPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6c3RhdHVzOlN1Y2Nlc3MiLz48L3NhbWxwOlN0YXR1cz48c2FtbDpFbmNyeXB0ZWRBc3NlcnRpb24+PHhlbmM6RW5jcnlwdGVkRGF0YSB4bWxuczp4ZW5jPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyMiIFR5cGU9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI0VsZW1lbnQiPjx4ZW5jOkVuY3J5cHRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyNhZXMxMjgtY2JjIi8+PGRzOktleUluZm8geG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPjx4ZW5jOkVuY3J5cHRlZEtleT48eGVuYzpFbmNyeXB0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8wNC94bWxlbmMjcnNhLW9hZXAtbWdmMXAiLz48ZHM6S2V5SW5mbz48ZHM6WDUwOURhdGE+PGRzOlg1MDlDZXJ0aWZpY2F0ZT5NSUlEZVRDQ0FtR2dBd0lCQWdJSkFKOEtaTTk5MWJ3NU1BMEdDU3FHU0liM0RRRUJCUVVBTUZNeEN6QUpCZ05WQkFZVEFsSlZNUXN3Q1FZRFZRUUlEQUkxT1RFTk1Bc0dBMVVFQnd3RVVHVnliVEVOTUFzR0ExVUVDZ3dFVFZwUVN6RVpNQmNHQTFVRUF3d1FjSEp0TG5CeWIyMWxaSGRsWWk1eWRUQWVGdzB4T0RFeU1qQXhNVEl4TXpSYUZ3MHlPREV5TVRjeE1USXhNelJhTUZNeEN6QUpCZ05WQkFZVEFsSlZNUXN3Q1FZRFZRUUlEQUkxT1RFTk1Bc0dBMVVFQnd3RVVHVnliVEVOTUFzR0ExVUVDZ3dFVFZwUVN6RVpNQmNHQTFVRUF3d1FjSEp0TG5CeWIyMWxaSGRsWWk1eWRUQ0NBU0l3RFFZSktvWklodmNOQVFFQkJRQURnZ0VQQURDQ0FRb0NnZ0VCQU0vWjJMV1ZGTks4TTIrcEtqd3VmV2prN1lyWHlvNlNvaG5oYW10QVkzWitVMFFpdG95dVdFcU9ZS3J6TXlDa0hMYXhEUkpNSCs2Qm4waEpoV25EMHVidGxxVGpDaXFPZFppTERISkRiblcxNE44OWlLSm9tQmNXNWJkcEJIcTFlQ1ZRcUZPaFBNMk4rRXZsZ0R0UW52UFQ1dEUrNkxhQWJCUURDWU5PRDFxM1FqQWdZZXNqM0FHN0pFa2tXRjQrQUNKSjhzZW4ydm1mTXg5Y2ZMTVB2RFRaY251ejRpdnE2cG9oVi9JVUM0QTF0VWtTbWZFdmJWSENJWi9aajdxaG11WW96bVE5ZG80dkMxcXA3dFNiRzdKdDZmSkE3NkVQa1JjL2RncXZ0ekM0SDJuaDBZdWFqRmcxNlRVQ0s2a2w0anRKbm95T0ZjRHZLTzFDcHlla0d2c0NBd0VBQWFOUU1FNHdIUVlEVlIwT0JCWUVGQXFqWkk3VFRSOFN2aG9UZ3NrZE8wUzNTTGxrTUI4R0ExVWRJd1FZTUJhQUZBcWpaSTdUVFI4U3Zob1Rnc2tkTzBTM1NMbGtNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJaHZjTkFRRUZCUUFEZ2dFQkFMaWo3dWk5QXpsZGRNZVllalZzOWtxNWl3Z2JNbEF6QkkxNU1BVHpMYm5TTnVISXZKazh4bUhxSTZXaFA1ek9kVDdSUUpIOFJHd0ZLeTdwNHM1L3p2d01PYzdkd3IwaCszZnIrTUFQVERpV0krOTNCOXArYlc4SFd6NnA5TVZsQys2ais3OVFsK09ZVXpscEF1KzJmZkkra3ZweDJKSkN3K3B0cHFhd1g1TCtpTmVOOElWelZ0M3g2TmQ1YnhvNGdsbXdyMHA3Uk1PVFZhRTF1ek9VOWZ5Y1A3U1lnVVNoNWUvbmpTZnJRSjU3RGJ1b3VHQUpzT0NxWmpGS0pJbjk2NkNBSkpmbzQ2Vm9WRDlDS1U2MHdMU0hsMk5yK2lyVEJwR1hIa2hzNHgwY1JjVVcxQlRyWUdwbmhicDg0dWlpQ1M3Sm9TcC9KcmZLOWUycXpvbnE2akE9PC9kczpYNTA5Q2VydGlmaWNhdGU+PC9kczpYNTA5RGF0YT48L2RzOktleUluZm8+PHhlbmM6Q2lwaGVyRGF0YT48eGVuYzpDaXBoZXJWYWx1ZT5QMGRSMERTTmNMTm9Kb2FxaDliU1A1czByeHRKNXhnTEJRLzM5MXJmZ0ZNVkRzd1VZRC9GNjhmY0FWNVRHcEZ4TCtraTcyS2pRZndFRXdMUnZ3YWNVa3E0SUZOdzIvODBteC9WSlkybGFHdXpuNXJoNlROOXpOZ25vMTlRNkpMSVhxcXA4VUZ1Z2w5RVlRWmg3REtSdmxnNVFtMjdMS3d3OFIxK2Q2ZVRwWWFjWEdQeWVHMmQ3ZGtEL0Z6M2Fyd1Z0Z2NMbUtmeHcxT1IrRnNaR0J0Snl0ckVzMkRpM21sTDNzdFlVK3orQWYxYkF4OWpRY2RSeTd6cGlHdlVGRlQ3Z1h5cXJPOWVFNmI2Y2Fmak02VnI3Z0U0VVpWNmpJYTJrbkJQTHQzM2lPS2JrR2hYdHJqajd3V0dPK0NDNE05WDhMWEhSMXJYenE2WU12cERoOWRyZWc9PTwveGVuYzpDaXBoZXJWYWx1ZT48L3hlbmM6Q2lwaGVyRGF0YT48L3hlbmM6RW5jcnlwdGVkS2V5PjwvZHM6S2V5SW5mbz48eGVuYzpDaXBoZXJEYXRhPjx4ZW5jOkNpcGhlclZhbHVlPkVQNFZzcE1xWFZPeFBFNWJRY1JHbVVYOGdCL2plQkxZcGlmblNScHNYWjhHanVIUXVsS1hFNDRtYUlZTE5MRTd3NWxyOC9acVo3aVliaHNBQXdWVW42a1JoaUZHN2tPcklRaDNGV3lJWjM4T0FNdzQzaTd0eWg5djVXMjNWSDlNQ1dnWXI0VE5MKzdKRHM0ZWo3akRhYXE3K0tYY3lLZEhjUVpLZDh5QVYvSm9UVUJTaWpHbHgxZHlQckxMZ0hMWU1nbERqWGJYd2R1MHZwc0VGMXE3QktsSU4xTEhXZ1pyMFkxbSszOHEvNldmeExHUkY4S0RhZGhhdDZuL0UxTVJXcS8vUHZObS80c1VxZE5BZEJoT01nYTBmdk5GbVBGSWk5M2l6N0RzWlN2L1k5U3NKb2dwcTBYYVk2eDNNVUY1T3FSTFhZMjBPSHN1WDJKbXlPNnNJNHhhNG05d0ZQK2w4NUU0bjZ6NS9lSERYOFdjM1RBMXVrYUE1N1RwdkVjakdwSDYvYXg5RlBnenExM21aWFlML2hlOG5qekY4dTNzakY5SFpFSEhBdlRRbDkxUk5IRnpLU1ZlS0E4cFhuVVZzMzhKUTF0YmE2RXg2bFVzM2kxU1VsQkRWeGN3VFFSTkNRLy95bXpXUCtwK0x2NSthb3FYTkZSZnV5UWJ5S0w3cUxtRFpUL3phNlZPZFlGaDMvNjA2YWk5MXJSbUFhbkM1eTZkSjlwOC9tWmI4OHIvS2FNR3VPZ0xSS1ljMGhqOFJOYnAyY2lsczVITktGKy9xeWhPWDdrS0c4WU9sK0hKNXNmTVlsbXBWR051R0ZyMWhHNU5makNPdVJUWGpaZGtldDFFNUF4S1NyME5xV3l1TUJyQVNmT294QjlENFdVYnd2M0pGTmFFeVo0ZDg4NklzSEgzbWQ0Sjd4SEJjaXU5YlBMQTlIZDUyOFVpOVpVVzFzamVWd0xRVDFMeGVDYUZ2T3BxcmdrOTNTODAwcngvMkQ1ektxTWw0eFFLNm9UL3R5VGNRTStwbit0M2pxaDM2VFVnTDJuUGhmL093RlcxbUVaeTlPYzdBSDRuaVRnVUhkeDF0dnVOSW82WFVsZk5aajBPcENFOGwzelpQMmdDbmVDTEUzREZzZmE5UE1UbGxYTERBRmdnZHRxOHc4Zjh0VkdzQVRSY2tFdG5DdTFkZWd6QldQSXlTR3JrUEJhcENQc0hmbUh3T1huZUZ5Zmg0cFFZT20yeGdXSUpOWjRuNVRpNnpwd3Q2eW00Rk4zUFVTUDBRalh4bVZ3Y3hkN1J3Q2JDQ0pQMnNOY0ZXeDRZVEZrZ2c4cEZjWHhiWENTblhuQ2VQYlBEd1BrTlU4N05KbkpaTDZCRWNxNUNZc2RrS1E5bXFNY0xGc2FpUU5abTBqRi9DOGdzNXhQSWNDWC91ZThuTW1sRDBQeHpRVU41UVQ2dGhNbEd4KzRmbGJlbDQ3VHpBMDMrWWc5aTJYSGF6TXNVSGdDMnErbHBVSDY1SWEvSjlKaGh6UUFIaGhCTGpRa1BaMlpaZnFxdFdKbytyZ2hyYXdiUUNqbSthQ2NQSms5RjlBTWI5NzRnbEdMd2J2UllXSVg0WlJnSk5ibjJmYzhLZy91dVRuRjJnLzh2OXQ5NUxzcWliQXNuc3R4SFkzaWJTSDFlVHQvd3JpcEh5S2gxSmJ1dXRSV0dQVnVlWkU2bEwzZWh3Vm1EVDNaMnNlUlZBRWc3blVRbzgzQmphZk4xWExNWXNtMEdnR01wY1lqbUhtVDBBaVlzc1FjSWVQV1RYRmJVMWlTWVR1TzdDUFV6Um5CM2dwUEdnS2pkNTNhUExNS1J0VklOc2NqRmlMd2EzYzdkK3gvLzJjOGxYRnc3ZmhPdEovSmF0VmIrVzhmaWR1akIzL05XVGRlQ0ZYOVhKeXNxRjBheHY0Q3dEMDR0M1pYdmt2TjZ5N0ZjREdBQVZDSXp3bnFBSXFsamlQL3lrRkRXTVMzL1ZIUnI3TmwwT3p5TjBqclIyNG94Sjk5VFB6Z0pMSHpiU1VXWU94SzN4R3I5YUhJVXJLTWFSWmF6Mkh1RWt5VEVNdzBBc0VFbTlYeG9waTlJZEZTL0VjZjRhTjhZQzFFbit2ckxYc3N1MTZvaG1HbEx1Zy83KzhvT014RzZzeE5lK01sYXhXbnFCNCtTUGhTa2h3VlJQNUJiNS9qb1NDWW45OElhamYvMzBTSDgvQlVRSk1BYjczelY1cUp5ZjFCZ2gzNUdxMnJBR1VuazVwNy9KV0JoZ3FkWUxrcFVhMGt3YVR4TFdzTTRzamprZXFQd3JrMG1NWWhXMWNNbi9wZUh4cGlLVVRjV1dzMVdrNEt1d0ZzM0FLS0NjVnA5Q1NHZ0luQWVrOHZzVTk1Yllyb0dmNHl5SkhJcTNpUlEyTnBFS3Q5UXJGZW5HRWh6Q1krVjhRWUlmdHhJamxKVWcvaWNtSVpBb01wY1ZaSmFscDB5empNN1RwVU05NUhlU0lsZk5BY09rendLZ3VjWkoyYmlHcXZvUmtRbmMxdVgwNzV1cjVqNEwrYnNUYkxuRU1zT3ZQQTkrczd1WGtubHpJTTNQeGc4TVRvdDdyUTlRVTZvek10NSttTmVtVFJteldxZVpxREJZVDZGakMyMXRlMGhKaEc3ZGlRRmJIajYzamxHZ05MaEFLOWhGdU92RHRZbXQ4U3pROCtPUWx3YWltOHZGQWFIZm1hZnNwb0lNRUZCazRmbXEyR1JBeGhPVjVWdHRuemdqN2ZHL2FlMkVjN0tDQzRkZHZ4UzhIQ3BpYWk4S0g5NjRvRjJhcktsS09xOG42eHdtdnYwRWNKQzE5Ym5wY2pJQU9YT3h1QmQzQ2hKQnFLTVk2UzJMRkdRa1Z6UDVYaGk3a3JBcWQwNXRiTEY0ZGV4MzM4SFZ6Y3Fra2UyWks4WnpWS1FOMnR2VzJlSWhUTndrbXgwNnh3enNCRUdNTXlmYmRXeGVrT3kwWnZXU3hwNmFrL3E1UjNOSUtpamd3MTR2YUxEOVBwelhxTXRKMlAyakFMU2JiTHJwWEJKY3hDd1RLbWJJSnYzN3lWVUtBYmFVdnlFOFlaQlFscVJsZ0Q4OWNhMTZXQmpBNDl6dlBlRzZoUWVxSCs5MDQyYlpjekpzV2I3Z2JqWjJXWHorQXVOMERzQnROWEprVEtSTGdtZVptTWZyRXFIQnlteHB4ZlFMODVjY2VvZGo1SGdKTXNmYmdlWlROWmRlZHdaVllLMVBKZ2kzaVh0WWxJMWdTcU1kZnJ4SGhic3EzRTBHeGNaNCtXSmNLblpmWG5jdWtQVHNEUEtJRVZMeVlmc0Q0SDdrb3hsMGdVb2Q2Yi81bmVKLzd0aWZHOC9RcXNkR2Q2WmM0UExoNWdyL2FJK0FnNFIrM1Q2ZU9aV2kzbTdiYzVIZCs3elJTS2J0eUwra2JMNFNtR2Y5SFQ0QzNZUFRna25EVHd6cURPWkxtWmFhSHRyY2trYTFUVUYra2k3VGJvcnlHbGJIZ1lWbEJKb3dtZUNVYjhyMzZPWmFCVFBnbzkrNXBnS044b2RkTEVNOE1ZWVlzZTdJeWVub25PcTBXSWx6QkprV2hWeDVYZ3J5TVRGM3dmcWt5S2NxcktJRXZ2b0NRclNwOU5rYnI3MEVhMXlQK2U4Um16dFJ1WFR1VlB2RndOa0NMWldXRTQxSlVXRFFlVmVNcUxGUnM0cVdabHMzUlh1U25nQVpLMmhLNUxPVUhNbW5zaTZsVG1SaGxUZmtCeStIQkV1RGFjT2NDcHlyNllUajBTV09RY0NYTnFKWUJUaGtRUFdyMVAyV2swMHBRV3JlYmNCb2diWERiYXRZVEVmTVIxMDd3V0JaRXRleHFiWjJPZGdQT2ZERlhPQ0xsM3Q3Yjh4ZDc3Ull0R0E5UTlJZnRBMEx2cWVjVzlOa293S0Rya1gzMS9pekhHeDBsdlF4VlhqdmN6bE1oUmhJczVwbHNubGFhcnVJRTV6Y1Y0TVVFeHgwazdtVnhGakV2L1F5RTNCMzcveExKNjM1NTB3bUpkd1F3UWROQjJYeTE2ckMxK2V2azdReE8yRmlFMGljUDI1cy80azlsRjc3QWFRem1zSGlBZVQzL1A2RTBoeXJiTGVTUTJBaW9XWFFHOGlrT0IwdStwQ296QitMSXVNS3dISU1oTmpBWEVuZlVkREdkdi9Vd1RxcnBxbHQvSG4zSGovVVVDUEpwSlE3V2ZYR2tLaTBSNEV2K3NRQ3ZCL3Rld2Evbmh4ME9tSG9xNFlvVkwrbGRBS3NJWGpCTUZ4T2hZaWxEQWpiRFIweUpKSFZyNml2ZGwveW0vRWtvMGpiZnYrN1VIanlpbTdpNFJCMTMvRTUvQnh4M0ZJc3VjQ0J0Y3hLSkJ1enl2WElaNDFaK05vTHk2N0ZOUS8zYzBYbU9meHU5MWRlZktIYW1xTUNzZGVDamFiUWhoU20xZXlCenhETmlMMGtMZzE1cDBZbGphbzlPZzk3SnVzYTFiMW9LNTI3aVZqcmhYSUY1VGVBYTBkUi9GRGUwa2pXWW84MmF1clFJNXVGdlFHMmFjSWZQa0gxdUlEVjR1T0ZxVXd1TFc5MUNQUENWNlR1elhyTGgyZkJoQjJOZFpicENWME5iM0pDR1c3SmZBc3RaZEE5QTgrYUd5Y3FLSm1mNXI4WStPNjVhSndJcmtla2h4RzY0Z0xMZWVsY3E0V0hYNFFMUHRDaU8wajZlWjdlZWVNVFNMdVM5K2V2QWoyZTJGRHYvLzdVUVhlM2J1REt0ZHZBbkowS0NJTGVxdjRiQnBvWkFDSnFobGMrRG80UXFPNlZkN2FScFBYVWxXQngyNzZJN0NRYUtwL3F1S3MrTXh4SXJvRVdmU2RlRzIvRnJRc25tampsanZDVTVESC9yb3F5N2lKQ2NmY3ZXbmZwWWJWcFZOc3BmNmJVRjlua1FpTXdpUDBVNC90QkVKL2d6eFp0YmoycjF6NzNwWHZwMW5mZnNpVGlMb2JGMUsyTEJBYUNuMVJDZjRVK3o5S0lXdlR0S2M3RFgyYnpNMkVKQXV5ZnJrWm1MZGVWeWY0V1A5d2wwb25aVUxjKzNyTm8rSDhwWlNsQjFWTWcvMzZjcHBnVWE4QWo2S0hJbVZHY1JHbXRubkRDTnhyNHgzdFgvcXNlM1JZazNHOVEvcnM0TWlVS1hzeUQ3V0o0czUxN2VSR2FOdzVEcmgwYjN4VElueWZmcFhGazZXMEQ4Wkc4UExCbGdRckFWdndFQ0pKZ2xQdjhna2FSaDRBVkhOMElrdFRmaXJjMGlESEYwMW0xZGluL3YvU09WeFl3TDRpdWdzSHNFRGQzODNORDR6SkpzSHBUNmdYZjJiQ0s4L1IvNFZSWEpEMFlCbGVMb3R4UzlaaTFXRVViZUJkeWtWUEdIaHkzbkhUV2laTGlFUTl2L01uTUx2b1NrZXpPMWhTdS94WnhrMC8waWlDWFNOY0s5Zk16NTRTenRNVGx6ZElMSUhPeDk1cGJjUm5ya3lSWlZLZnZMTW5yZEtkNVhCeWdrcTYwUWtyc3llMWJ0SGNJcjZhbTFOa2FpaGU5alRKd3FGUnlUeTU4ZCtvK0VrL3hkOHpSMkxXTUp1T1Rsb21KTGZncTZzL2dkOGoyRVBSZWFuQ3FGOWFpZWdDOGN4Q1VjQnpEaWVzcjJPYkQ4bjYzUm5CNVdvemswQ1prbUFqSTVZLzFGRU9yWmZsYm1JSzFKSytXNU11Y2YwbzFZdHUxRVJIazBKRFM5T3IzNXY4QWNBeEZiZFlQb3EzQ3hCZmp1MGVvS0VibVNlakgzMk16SVRTekJ5N05VSk5WVlltcHp1aU16eTdDeGswdjJvbzREOGVMcEhYUm5KZW55UXFGTmp6TndLS291Yzh2NmVma1NiN2E2aHl0UUNZT0F5bnZjRXVOSzlOQmM5ckJTU2Mwc0Zyc1hFWjN5RE5uMGRVVGZ6OFRCUXBwWnErSWFzMzBBajUydmt1cnpqMTR0TDRkSDNubkFLdzBhNTdkUGY1Mll1NXRLTmREbWtrVVpEZ3JLd09LbEFOcWxWQ2xtazBPSFhPZkk3TDRIeGlaVzBxTW9RK0p5OVR5RjhwQnowbjhGRURHUGJobEtCc25WNFdwZm1ZZURHcnkvSno1QlZseEZhc1ZSdFRua3BlSjI2eVlvZm9HQ01lcVNQcHhVcmhPbWtINHVOamxBM1NSdndaTE5DY3V6bTNZWGpBbFUvYlJKK2tmTlJVYnNDUTJpc0JqWGtyQ1Z6MFB1Q3FKZzNIVTBOVHFFMXAwOFN2MmRYTzd6azltRE4wZWx0VDFJL2hzWkh2SDZ6dVVwTnpCVG8vL1BKRnEyRGFLZ3NySVpDMEExNUFvOE5mRDVYQ0VxSDdjbU14eWcvUXU4NHVEVXBXTFFUelBYVWswbDZaK2xuQkZFWi9MLzJ4NWUzNUIxbG9MV253SDI5blJDRTVXdGZxcEVVNnpoa01nZFBySytVdzZkQWNwejl3eDZvUFJEVHpaNXBlQnN2TmJhTlNQRzFyYlRaSWt3NlVPN2ZCQmxmd1RFSkszQ1cwODB0K2djQ0Nxdk9ERXdZV1IvbFNIRHhEdWJ3ZlpEMWVDVDlUcEpoNExmWTM2ZDJhWWl4ZHBDOE5RVzg5YmpLaFcyOW9rVEkxam5jNG1TQUxQaEV4L2kyRnRrbTQzeDhoYmRQa2oyaDFWVTdrRFdkV2RTZUcrbUUyQ1lsWlZpQS9RUXZrbGFXNmR4MjVxVERDdWZHSUs1TkhUdTcxaWlkdnRaTUhJNG16VEdzdThUVjdGcERxYk9qeXh0QlBYZE5zVkl6amNwTlV1aVR6Q3g5N2xhZnJDTTJleUc4bFEvWXZuclYyVHJBcGt5N2g3c0w3cjdweWNuaUZUWEE3Z2loU3ZJMTM0UVZXeUJVUkpqQnN5SEVVWlJmdFNSRm5mU0JsVXhIdS9VL0xnYmVtVm5oL3R5Y3R1S0srK29FT05Sd1IyQlFva3BrS1VVc3hKMzlITGtpRUVWajFySFo0azUxT05zVmpRa21OamYwanVCKy92a0JyK2h2eVd6RDdLUlFUcWJqYUF4REw3ZDZmTm9vWVJBQnNEUTVWUHpHM2ZGc1dnU1NMMWVIM3VSRk5LTklSWXFjSVplMWM0d2t4em94UjlKelU0MnZ5dUdScXNVQWFhWi9vYUdOZ2x1eVJ3OUJMVXlBTTFKL2UyeTdsVy9DdXlVbnFJU1AxZnR4eGZVc0U4REMwbzZ6NUNJaWpJMVhFVDNLZ2krUERrRkUzOFhwMlZhQmtjRkluZjRZV3huQVo1OFhpamd2WEhPbDNWdVVjY3N1c3hnbWgxOWZWU0xLZmNsd2U4ZlQ5c1drZzBWSDROOXVZdTF2UVZNZ2t0eHpubEJWMnFwVlFSMUY4cGRCdjhEcHo0SU9McFQzWEpiMEJVYjI5NGNGTTRlUU5DckFGSHNvTWVBay9pOE1PYzVIL0EyS1ZuVmNVVkticUhLWXN4QzYvb3FiVm1qUitFbkliWnkyclFQSjdIbmx6QlVJbUZOVm5HVGt4RkVxMWRhYnNqZm1pSFZoTVNYaHd6OGhCbkF6SDRPN3Rsb1I4WkJtbnNVcy9VWjZnOHJhbXVKRUljamJNNnE4Z0VJWjNzb1NrTW1wSzhFa1N2ZEhlS0txZkdUekRhbDJtZG9BeUxZQlNwd3kzdk5LaURrazJsb1V1NmJZbWp4MzI0U0NQWVVGWXlnM1FDUERyTi9DKzZQNUZPbGtoUzFaVytBS2hCTmVzcytqRWllek5wTzJmVlpodFlZanE2eVFoUElzdUt4K2pRa3ZMUWdyaHhpRnc3Rjh5dCtmT2RsQnUxN0dRdzFYcDJzcE1VQk9VTzFOK3V1RWJDS0wwRmFCNW9laTRjRHlielVtT2xmYkZwZ0QzaTBhWmErTnNsM2RKRGY4RlZaMUVsWFRVMHo2RVluVkhJcWw3aHlGK0NUbXVGMUVkRTRVMjdaTXJYMHVabnlmOGJDVGlvblRJSkRsdDI5NERKOEY3bVB1c3JNU1VWMzZjTVJvMEsxaGJZUWt3WkJoMkdLdzhjT1VOT1o0NHNjTVNTSlNNaW9ZRzBrZWhWM2VFNGJvQW9RTXZmMGxibTJHT01HUmVINnU0dG1BYnNxUlVBclVIR0JHcEtJbXE3amwrNjFYZTlzNXV1ZXZvb2tNVWk4N3hhdjVMa0dFanJOQmxSSlk3VmhIaEpwNW4xOWgxNWVVcENacHVIa0d2U3RTazZFeXl3Qk0yaSsydVVYb2d0TzBWdTh4MlZZVTZLa29ZcVpDT2ZqMTFYbVdTbDllSGtGNEZ5ZTNNTnRsTytZRWlueUhWOWdQWndVQnRsajV6cXJzTnBLVlVtMWd2Q1plSFVtSWJMdjR5b3BRcHVCclFyUUJTb0YxY3VDYnNvLzBpdFNsakhFK1M1NDBYdnVDMmJGRDhOa0E5UkR3dEJyMEt6WXUxbFhBN0FxMW5uZnoxZnFreFpWSHY2RmhiblZ0QkZCcGZkVlVzWXdkNFZlWDJMMVI0MklDYk4zd3NzcTJWN2I3bnhNRHJrdjErbit3dmFoVHFLMVVWZFVUOHFIR2JyL2RTWUlVb0VURDdqaGh1eUdhMGNHb05YeVlUT1VEZlpSaFV3Z2F3WFU3alQzUFVQdHU4YkNZN2ZzWHg2NTZvaWl0LyszYTFmVGc2VDdUd3RwblBWOThVZ0tHOHVCMmMwcVI0VlZCWWlmNHlyVzdWSW16UHE5bjNBTERJQjBvbUdVVWwwQXo5K1ZjbmhIeFlwQnI5RVFqWTVnTnhwc21KNXVrSnZYMU44cTVjNFZIakUwTXRFbDkvZ1VQcXhlcWg1OEpvcnN4OHFSTk1TNzlJNkNHaG42ZUhkaERBRVVPOTdEaTVFamtudmNQMGdrUFNNUWFIN1NzYnZRTGFjT3BtQmN0M3kwVWxBYzQ2dWNPT0p5Q2VjaXYyNlloWStzN3Rwa0kvUG5QblpRREJ2N0N0S214cTZuRmkrbkVROEowZXVnOTR3NUZ0SCs5RCtnRDgyenUrdG9RN2U0ZUlxeDdsTEY4SmF1TDJDbS8yTCttUXcwaTFvdVVyYU9GT2N2M0lXU283b2xvMWRBd2FZMVd4R0xTdFFIeXpPRlRQWFpxSmhxRFNNZ3o2UUQ0VE53M0kyS2o2NXpoNFp4RTN1MzU2a1kvVkNSSzdRZFlnZzhJNGlMMCs3cWVDZnhZNzVLaXo4MkNNMzV1MmdpVWRtQzhvZGd1NXY4ZWFZSng0UWxhRTNwS2lUMU1MVEJ2L0ZDTkRhSmsxZjhuQ2tMVEY1cXdFQ3pnM3Bwak9Ua0cxcWlNdldXZ1FIaHNyQlo1eTBuOUEydGd2bHJ0cmF5WGFtV1liV1o0RXNFOHp3cTErQUJUUEF1bGI2cFNwVk9SR2dNUkxZcnVWVk5ZN1djR3lMSXFwaHlnQjJuMWdwR2g4aTNmRVJ1Z05rcWoxUW9CZVZ0cjBtOUhNcXIyL0JLTmdHdFZ2VXhJelFsbkg1N0Q2Zk9TUWovSXhOYU5mZEk5RjZvLzQxeHBnM0RJeGVXQnNBeWxPdUdWQlUwcTcweHB4ZDQ1ejlsMWYyR1BacnZRVjlOUmJKUlhHQmdqSFhrbCs4aVc2S2RKUmtzcHNXQWJ2Zk8yVkI4QndQRzBoNHdpTFF6cFpXVktnaDd0aFlPOVNVeENtdnIxMThZRStrZnpQRVUyMXh5Wk0zYlFSNVdpRHRLejVqNFkzT1F3TUxKeUhIYmlCMDVyK1g1REhnTVR6Mkt3RlhWVG9KaDNOVDdnS1VGcXNqNmp0NnAyR3pLZ1liSjlRSmxIY2Y0SW11RHBqcExVRWsrUHNJakF3aHBTK0ZGdWxPSFdzWERqakM0cnFFb0IzOFhub0JmNWRVRE8xWTB4U0VxYnhqR21mQnpoWGxpSVZNS2gxOHpSYkJRbU11MEdFSCsvdlNRZEFYaEM4R1pvaUNaUU55cGNsZlFsK2FiRFBnVUk3UzBhcXZ4ZmtTSnJKeERkaWJHVU9CSU90SHJWVTk3U0cxQmwyRWo1TitQVlpaT3BSeEdneTZ3WHFKMGJQQ0VOVmRGZFQ2YklabFMzU0RwVE5NbzFQOHFVY3YwTGcwV2lvN0E2QlBtcEtTcXBtdlBQZTlHdXZNQk1vcUl6WHBTcm45Mi8zOWNvNDwveGVuYzpDaXBoZXJWYWx1ZT48L3hlbmM6Q2lwaGVyRGF0YT48L3hlbmM6RW5jcnlwdGVkRGF0YT48L3NhbWw6RW5jcnlwdGVkQXNzZXJ0aW9uPjwvc2FtbHA6UmVzcG9uc2U+';
		if (!isset($data['SAMLResponse'])) {
			$_SESSION['egisz_guid'] = $guid;
			// формируем SAML запрос
			$request = '<?xml version="1.0" encoding="UTF-8"?>
				<saml2p:AuthnRequest
					xmlns:saml2p="urn:oasis:names:tc:SAML:2.0:protocol"
					AssertionConsumerServiceURL="' . str_replace('&','&amp;', $redirect_uri) . '"
					Destination="' . $egisz_path . '" 
					ForceAuthn="false" 
					ID="' . $guid . '" 
					IsPassive="false" 
					IssueInstant="' . date('c') . '"  
					ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" 
					Version="2.0"> 
					<saml2:Issuer xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion">' . $client_id . '</saml2:Issuer> 
				</saml2p:AuthnRequest>
			';
			$get_query = array(
				'SAMLRequest' => urlencode(base64_encode(gzdeflate($request))),
				'RelayState' => urlencode($guid),
				'SigAlg' => urlencode('http://www.w3.org/2000/09/xmldsig#rsa-sha1')
			);

			$str = "SAMLRequest={$get_query['SAMLRequest']}&RelayState={$get_query['RelayState']}&SigAlg={$get_query['SigAlg']}";
			// подписываем
			if (($sign = $this->sign($str)) === false) {
				$this->textlog->add('Ошибка подписи запроса в ИА ЕГИСЗ');
				$_SESSION['esia_error'] = 'Ошибка подписи запроса в ИА ЕГИСЗ';
				$this->redirect('/?c=portal&m=promed');
			}

			$str .= "&Signature=" . urlencode($sign);

			// Перенаправляем в ИА ЕГИСЗ, чтобы пользователь дал разрешение на аутентификацию
			$this->textlog->add('Перенаправляем в ИА ЕГИСЗ, чтобы пользователь дал разрешение на аутентификацию');
			$url = $egisz_path . '?' . $str;
			$this->redirect($url);
		} else {
			try {
				// получили ответ, разбираем его
				$xml_string = base64_decode($data['SAMLResponse']);
				$xml = new SimpleXMLElement($xml_string);

				// 0. Проверяем, что ответ пришёл на тот запрос, что отправляли
				$guid = $xml->attributes()->InResponseTo->__toString();
				if (empty($_SESSION['egisz_guid']) || $_SESSION['egisz_guid'] !== $guid) {
					$this->textlog->add('Неверный GUID в овтете от ИА ЕГИСЗ');
					$_SESSION['esia_error'] = 'Неверный GUID в овтете от ИА ЕГИСЗ';
					$this->redirect('/?c=portal&m=promed');
				}

				// 1. Проверяем, что запрос подписан и подпись верная
				$doc = new DOMDocument();
				$doc->loadXML($xml_string);
				$signedInfoNodeCanonicalized = $doc->getElementsByTagName('SignedInfo')->item(0)->C14N(true, false);
				$this->load->helper('openssl');
				$pub_key_id = openssl_pkey_get_public(getCertificateFromString($this->esia_config['egisz_crt']));
				$SignatureValue = $xml->children('ds', true)->Signature->SignatureValue->__toString();
				$ok = openssl_verify($signedInfoNodeCanonicalized, base64_decode($SignatureValue), $pub_key_id);
				if ($ok !== 1) {
					$this->textlog->add('Ошибочная подпись ответа от ИА ЕГИСЗ');
					$_SESSION['esia_error'] = 'Ошибочная подпись ответа от ИА ЕГИСЗ';
					$this->redirect('/?c=portal&m=promed');
				}
				// подпись верна, сравним теперь хэши
				$DigestValue = $xml->children('ds', true)->Signature->SignedInfo->Reference->DigestValue->__toString();
				$SignatureNode = $doc->getElementsByTagName('Signature')->item(0);
				$SignatureNode->parentNode->removeChild($SignatureNode);
				$canonicalized = $doc->C14N(true, false);
				$calculatedDigestValue = base64_encode(pack("H*", sha1($canonicalized)));
				if ($calculatedDigestValue !== $DigestValue) {
					$this->textlog->add('Ошибочный хэш в ответе от ИА ЕГИСЗ');
					$_SESSION['esia_error'] = 'Ошибочный хэш в ответе от ИА ЕГИСЗ';
					$this->redirect('/?c=portal&m=promed');
				}

				// 2. Достаём данные из запроса
				// достаём зашифрованные данные
				$ENC_DATA = $xml->children('saml', true)->EncryptedAssertion->children('xenc', true)->EncryptedData->CipherData->CipherValue->__toString();
				// достаём зашифрованный ключ
				$ENC_KEY = $xml->children('saml', true)->EncryptedAssertion->children('xenc', true)->EncryptedData->children('ds', true)->KeyInfo->children('xenc', true)->EncryptedKey->CipherData->CipherValue->__toString();
				// расшифруем ключ своим закрытым ключом
				if (isset($this->esia_config['key_pass'])) {
					$priv_key_id = openssl_pkey_get_private($this->esia_config['key'], $this->esia_config['key_pass']);
				} else {
					$priv_key_id = openssl_pkey_get_private($this->esia_config['key']);
				}
				openssl_private_decrypt(base64_decode($ENC_KEY), $KEY, $priv_key_id, OPENSSL_PKCS1_OAEP_PADDING);
				// расшифруем данные полученным симметричным ключом
				$DATA = openssl_decrypt(base64_decode($ENC_DATA), 'aes-128-cbc', $KEY, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
				// удалим мусор, оставив XML-ку
				$DATA = substr($DATA, strpos($DATA, '<saml:Assertion'), strpos($DATA, '</saml:Assertion>') + 17 - strpos($DATA, '<saml:Assertion'));
				// начинаем парсить
				$xml_data = new SimpleXMLElement($DATA);
				// сохраняем в сессию данные, необходимые для выхода из ИА ЕГИСЗ
				$NameID = $xml_data->children('saml', true)->Subject->NameID->__toString();
				$SessionIndex = $xml_data->children('saml', true)->AuthnStatement->attributes()->SessionIndex->__toString();
				$_SESSION['egisz_data'] = array(
					'NameID' => $NameID,
					'SessionIndex' => $SessionIndex
				);
				// будем искать СНИЛС
				$snils = null;
				// ищем данные пользователя в XML
				foreach ($xml_data->children('saml', true)->AttributeStatement->Attribute as $attribute) {
					if ($attribute->attributes()->FriendlyName == 'personSNILS') {
						$snils = str_replace(array(' ', '-'), '', $attribute->AttributeValue->__toString());
						break;
					}
				}
			} catch (Exception $e) {
				$this->textlog->add('Некорректный ответ от ИА ЕГИСЗ: ' . $data['SAMLResponse']);
				$this->textlog->add('Ошибка: ' . $e->getMessage());
				$_SESSION['esia_error'] = 'Некорректный ответ от ИА ЕГИСЗ';
				$this->redirect('/?c=portal&m=promed');
			}

			// ищем пользователя по СНИЛС
			$mp = $this->queryResult("
				select distinct
					mp.MedPersonal_id as \"MedPersonal_id\"
				from
					v_MedPersonal mp
					inner join v_PersonState ps on ps.Person_id = mp.Person_id
				where
					ps.Person_Snils = :Person_Snils
                limit 2
			", array(
				'Person_Snils' => str_replace(array('-', ' '), '', $snils)
			));

			if (!empty($mp[0]['MedPersonal_id'])) {
				if (count($mp) > 1) {
					$_SESSION['esia_error'] = 'Не удалось однозначно определить Пользователя: найдено более одного человека с указанными данными. Обратитесь в службу технической поддержки для проверки данных';
					$this->redirect('/?c=IaEgisz&m=logout');
				} else {
					$user = pmAuthUser::findByMedPersonalId($mp[0]['MedPersonal_id'], true);
					if (is_array($user) && !empty($user['Error_Msg'])) {
						$_SESSION['esia_error'] = $user['Error_Msg'];
						$this->redirect('/?c=IaEgisz&m=logout');
					} else if ($user && $user->loginTheUser(5)) {
						$this->redirect('/?c=promed');
					} else {
						$_SESSION['esia_error'] = 'Авторизация пользователя не выполнена. Учетные записи пользователя не  найдены. Обратитесь к администратору системы для уточнения данных учетных записей пользователя';
						$this->redirect('/?c=IaEgisz&m=logout');
					}
				}
			} else {
				$_SESSION['esia_error'] = 'Не выполнена идентификация пользователя. Обратитесь к администратору системы для уточнения персональных данных пользователя';
				$this->redirect('/?c=IaEgisz&m=logout');
			}
		}
	}

	/**
	 * Логаут через ИА ЕГИСЗ
	 */
	function logout($data)
	{
		$guid = GUID();
		$egisz_path = $this->esia_config['egisz_path'];
		$client_id = $this->esia_config['client_id'];

		if (isset($_SESSION['egisz_data']) && !isset($data['SAMLResponse'])) {
			$_SESSION['egisz_guid'] = $guid;
			// формируем SAML запрос
			$request = '<?xml version="1.0" encoding="UTF-8"?>
				<saml2p:LogoutRequest
					xmlns:saml2p="urn:oasis:names:tc:SAML:2.0:protocol"
					Destination="' . $egisz_path . '" 
					ForceAuthn="false" 
					ID="' . $guid . '" 
					IsPassive="false" 
					IssueInstant="' . date('c') . '"  
					ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" 
					Version="2.0"
					xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion">  
					<saml2:Issuer>' . $client_id . '</saml2:Issuer>
					<saml2:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">' . $_SESSION['egisz_data']['NameID'] . '</saml2:NameID>
					<saml2p:SessionIndex>' . $_SESSION['egisz_data']['SessionIndex'] . '</saml2p:SessionIndex>
				</saml2p:LogoutRequest>
			';
			$get_query = array(
				'SAMLRequest' => urlencode(base64_encode(gzdeflate($request))),
				'RelayState' => urlencode($guid),
				'SigAlg' => urlencode('http://www.w3.org/2000/09/xmldsig#rsa-sha1')
			);

			$str = "SAMLRequest={$get_query['SAMLRequest']}&RelayState={$get_query['RelayState']}&SigAlg={$get_query['SigAlg']}";
			// подписываем
			if (($sign = $this->sign($str)) === false) {
				$_SESSION['esia_error'] = 'Ошибка подписи запроса в ИА ЕГИСЗ';
				$this->redirect('/?c=portal&m=promed');
			}

			$str .= "&Signature=" . urlencode($sign);

			$url = $egisz_path . '?' . $str;
			$this->redirect($url);
		} else {
			// пришёл ответ на разлогинивание от ИА ЕГИСЗ
			$esia_error = null;
			if (isset($_SESSION['esia_error'])) {
				$esia_error = $_SESSION['esia_error'];
			}
			session_destroy();
			session_start();
			if (!empty($esia_error)) {
				$_SESSION['esia_error'] = $esia_error;
			}
			$this->redirect('/?c=portal&m=promed');
		}
	}

	/**
	 * Подписывает строку подписью PKCS7
	 */
	function sign($str)
	{
		if (isset($this->esia_config['key_pass'])) {
			$priv_key_id = openssl_pkey_get_private($this->esia_config['key'], $this->esia_config['key_pass']);
		} else {
			$priv_key_id = openssl_pkey_get_private($this->esia_config['key']);
		}

		$signature = null;
		openssl_sign($str, $signature, $priv_key_id, 'sha1');

		$pub_key_id = openssl_pkey_get_public($this->esia_config['crt']);
		if (openssl_verify($str, $signature, $pub_key_id) !== 1) {
			$_SESSION['esia_error'] = 'Ошибочная подпись запроса в ИА ЕГИСЗ';
			$this->redirect('/?c=portal&m=promed');
		}
		return base64_encode($signature);
	}

	/**
	 * Перенаправление
	 */
	function redirect($url)
	{
		header("Location: {$url}", TRUE, 302);
		die();
	}
}