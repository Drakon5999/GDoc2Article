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
- В GoogleDocs создайте папку "GDoc2Article".
- Дайте к ней доступ девелоперскому аккаунту.
- Откройте в браузере youproject.ru/vendor/drakon5999/quickstart.php.
- В проекте появится папа data/GDoc2Article/ с содержимым из GoogleDocs.
- Смотрим quickstart.php делаем как там.
- Enjoy!

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
