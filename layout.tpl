{WHAT:}
	<h1>WEB сервис для Google Drive</h1>
	Нужно передать 
	<ul>
		<li><b>/ID</b> файла</li>
		<li><b>/folder/ID</b> папки</li>
		<li><a href="/{root}/public">/public</a></li>
		{public::pub}
	</ul>
	public настраивается в .infra.json
	{pub:}
		<li><a href="/{root}/public/{~key}">/public/{~key}</a>/name/body</li>