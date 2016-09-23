# GDoc2Article - достаём документы из GoogleDocs

## Установка через composer

```
{
	"require":{
		"drakon5999/gdoc2article":"~1"	
	}
}
```

## Использование

- Нужно [создать девелоперский ключ](https://console.developers.google.com) и загрузить его в папку data/client_secret.json.
- В GoogleDocs откройте доступ на любой файл на созданный ключ.
- Откройте в браузере путь ```/vendor/drakon5999/gdoc2article/?id={ИД документа}``` ИД документа можно подсмотреть в адресной строке открытого документа.


### Как создать девелоперский ключ
В [Google APIs](https://console.developers.google.com) если в первый раз прокликиваем обучающие окошки.
 - под стрелочкой в левом углу создаём новый проект. Или на этой [странице](https://console.developers.google.com/iam-admin/projects).
 - переходим на панель управления созданного проекта и включаем API, нажимая на плюсик.
 - в открывшемся поиске находим "Google Drive API" и Включаем его.
 - переходим на страницу [Учётные данные](https://console.developers.google.com/apis/credentials)
 - Какой сервис вы используете – Google App Engine или Google Compute Engine? Ответ - Не использую.
 - выбираем создать и пользуемся помощью [мастера](https://console.developers.google.com/apis/credentials/wizard) создания учётных записей.
 - Выбираем роль как минимум "Читатель", тип ключа JSON.
 - Имя выбираем любое
 - Полученый ключ сохраняем в папке data/ под именем client_secret.json.
 - Узнаём имя сервисного аккаунта на странице [Сервисных аккаунтов](https://console.developers.google.com/iam-admin/serviceaccounts/project).
 - Создаём папку, в примере GDoc2Article, и даём доступ этому аккаунту.
 - [Управлять доступами](https://console.developers.google.com/iam-admin/iam/iam-zero), дать доступ клиенту, сотрудникам


## Опции
```php
	class GoogleDocs {
		public static $conf = array(
			'production' => 'kemppi-nn.ru', //Адрес продакшина, для замены ссылок из гуглдокс на ссылки относительно корня сайта
			'certificate' => '~client_secret.json' //Адрес файла с авторизацией гугла
		);
	}
```