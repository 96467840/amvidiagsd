﻿0. Формат
	0.1. До любых проверок происходит очистка начальных и конечных пробельных символов
	0.2. Комментарии в файлах. Если строка начинается с символа '#', то она полностью игнорируется
		с учетом п. 0.1. строка считается комментарием если она начниается с любого количества пробельных символов и символа '#'
	0.3. Ключ и значение разделяются символом ':'. Затем к ключу и значению применяется пункт 0.1. (удаляем начальные и конечные пробельные символы)
		Значение дополнительно обрабатывается: экранируются символы '\' и '"' и всегда удаляется, то что между символами '{' и '}'. 
		В некоторых случаях значение очищается от html разметки (например, description в статьях)

1. файл settings.txt
	этот файл содержит общие настройки GSD
	1.1. sitename:	Amvidia (значение по умолчанию: Amvidia)
	1.2. sitealtname: Sample of \' sadf   (значение по умолчанию: нет) это поле используется в https://developers.google.com/search/docs/data-types/sitename
	1.3. sitelogo:	https://amvidia.com/images/amvidia_logo.png  (значение по умолчанию: images/amvidia_logo.png)
	1.4. siteurl:	https://amvidia.com  (значение по умолчанию: https://amvidia.com/)
        1.5. sameas: ссылка на соц сети, таких ключей может быть несколько! 
	1.6. homename:	название главной страницы для хлебных крошек (значение по умолчанию: берется из 1.1.)
	1.7. breadcrumbs_enabled:	0 или 1 показывать или нет хлебные крошки (значение по умолчанию: 0)
	1.8. articles_enabled:	0 или 1 показывать или нет статьи (значение по умолчанию: 0)
	1.9. search_enabled:	0 или 1 показывать или нет поиск (значение по умолчанию: 0)
	1.10 search_url:	шаблон для поиска (например, https://amvidia.com/search?searchword={search_term}&searchphrase=all)


2. Статьи
	Если articles_enabled: 1, то на страницах со статьями будет позан https://developers.google.com/search/docs/data-types/articles
	Если нужно какое то значение переопределить, то нужно создать файл в папке microdata/articles
	Именование файлов возможно 2 способами "a.{article ID}.txt" или "m.{menu ID}.txt" 
	Названия ключей:
	2.1. "url"         => адрес статьи (в теории он тут правильно сам должен вычисляться)
	2.2. "title"       => название (по умолчанию: берется из названия)
	2.3. "description" => описание (по умолчанию: сначала пытаемся взять из introtext (не нашел как это заполнить в админке может ты знаешь), затем берем полный текст )
	2.4. "image"       => картинка (можно абослютный урл или относительный images/amvidia_logo.png) (по умолчанию берется из статьи (вторая вкладка "ссылки и картинки") сначала пытаемся взять "image_intro" потом "image_fulltext")
	2.5. "created"     => дата создания формат yyyy-mm-dd hh:mm:ss,
	2.6. "modified"    => измения
	2.7. "published"   => публикации
	2.8. "author"      => имя автора (по умолчанию берем из "author alias" created_by_alias)
	2.9. "authorlogo"  => фото автора и паблишера
3. Software
	нужно создать файл в папке microdata/software
	Именование файлов тока так "m.{menu ID}.txt" 
	Названия ключей:

	name: name
	os: operatingSystem
	category: applicationCategory
	ratingvalue:4.6
	ratingcount:100500
	price:200
	currency: USD
	screenshot: path/to/image.png
	published:
	version
	publisher: (по умолчанию Amvidia)
	publisherLogo: (по умолчанию images/amvidia_logo.png)
	officialUrl
	downloadUrl
	reviewer
	reviewrating
	reviewdate


 
