<?php
//
// Copyright (C) 2004 W.H.Welch
// All rights reserved.
//
// This source file is part of the 404SEF Component, a Mambo 4.5.1
// custom Component By W.H.Welch - http://sef404.sourceforge.net/
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Please note that the GPL states that any headers in files and
// Copyright notices as well as credits in headers, source files
// and output (screens, prints, etc.) can not be removed.
// You can extend them with your own credits, though...
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
// 
//  Russian translation by Dmitry D, September 2007
// 
// {shSourceVersionTag: Version x - 2007-09-20}
// Dont allow direct linking
//  Russian translation by Dmitry D, October 2007
// 
// {shSourceVersionTag: Version x - 2007-10-22}
// Dont allow direct linking
defined( '_JEXEC' ) or die( 'Доступ запрещен.' );

define('_COM_SEF_404PAGE','Страница ошибок 404:');
define('_COM_SEF_ADD','Добавить');
define('_COM_SEF_ADDFILE','Файл индекса по умолчанию:');
define('_COM_SEF_ASC',' (asc) ');
define('_COM_SEF_BACK','Вернуться в Панель Управления sh404SEF');
define('_COM_SEF_BADURL','Старая Non-SEF ссылка должна начинаться с index.php');
define('_COM_SEF_CHK_PERMS','Пожалуйста, проверьте разрешения на ваш файл и убедитесь, что этот файл может быть прочитан.');
define('_COM_SEF_CONFIG','sh404SEF<br/>Конфигурация');
define('_COM_SEF_CONFIG_DESC','Настройка функционирования sh404SEF');
define('_COM_SEF_CONFIG_UPDATED','Конфигурация обновлена');
define('_COM_SEF_CONFIRM_ERASE_CACHE', 'Вы уверены, что хотите ОЧИСТИТЬ кэш ссылок (URL)?   Это рекомендуется сделать после изменения конфгурации. Для создания кэша снова, необходимо вновь зайти на свой сайт и проийти по ссылкам, или же лучше(!) создать карту сайта.');
define('_COM_SEF_COPYRIGHT','Copyright');
define('_COM_SEF_DATEADD','Дата добавления');
define('_COM_SEF_DEBUG_DATA_DUMP','ДЕБАГ ДАМПА ДАННЫХ ЗАВЕРШЕН: Загрузка Страницы Завершена');
define('_COM_SEF_DEF_404_MSG','<h1>404: Не найдено</h1><h4>Извините, но содержимое, которое Вы запросили не найдено</h4>');
define('_COM_SEF_DEF_404_PAGE','Страница ошибки 404:');
define('_COM_SEF_DESC',' (desc) ');
define('_COM_SEF_DISABLED',"<p class='error'>ПРИМЕЧАНИЕ: Поддержка SEF в Joomla/Mambo выключена. Для использования SEF, пожалуйста, включите ее из страницы SEO<a href='".$GLOBALS['shConfigLiveSite']."/administrator/index.php?option=com_config'>Глобальная Конфигурация</a></p>");
define('_COM_SEF_EDIT','Редактировать');
define('_COM_SEF_EMPTYURL','Необходимо ввести Ссылку (URL) для перенаправления.');
define('_COM_SEF_ENABLED','Включено');
define('_COM_SEF_ERROR_IMPORT','Ошибка при импорте:');
define('_COM_SEF_EXPORT','Зарезервир. Выборочные ссылки (URLs)');
define('_COM_SEF_EXPORT_FAILED','Экспорт закончился неудачно!!!');
define('_COM_SEF_FATAL_ERROR_HEADERS','FATAL ERRPR: Заголовок уже отправлен');
define('_COM_SEF_FRIENDTRIM_CHAR','Допустимые символы:');
define('_COM_SEF_HELP','sh404SEF<br/>Поддержка');
define('_COM_SEF_HELPDESC','Нужна помощь по sh404SEF?');
define('_COM_SEF_HELPVIA','<b>Поддержка доступна через слудующие форумы:</b>');
define('_COM_SEF_HIDE_CAT','Убрать Категории');
define('_COM_SEF_HITS','Просмотры');
define('_COM_SEF_IMPORT','Импортир. Выборочные ссылки (URLs)');
define('_COM_SEF_IMPORT_EXPORT','Импорт/Экспорт ссылок');
define('_COM_SEF_IMPORT_OK','Выборочные ссылки (URLs) успешно импортированы!');
define('_COM_SEF_INFO','sh404SEF<br/>Документация');
define('_COM_SEF_INFODESC','Посмотреть Сводку и Документацию по sh404SEF');
define('_COM_SEF_INSTALLED_VERS','Установленная версия:');
define('_COM_SEF_INVALID_SQL','Неверные данные в файле SQL :');
define('_COM_SEF_INVALID_URL','НЕВЕРНЫЙ URL: данная ссылка требует правильный Itemid, но он не найден.<br/>РЕШЕНИЕ: Создайте пункт меню для данного элемента. Вам ненужно его публиковать, просто создайте его.');
define('_COM_SEF_LICENSE','Лицензия');
define('_COM_SEF_LOWER','В нижнем регистре');
define('_COM_SEF_MAMBERS','Форум Участников');
define('_COM_SEF_NEWURL','Старые Non-SEF ссылки');
define('_COM_SEF_NO_UNLINK','Невозможно переместить загруженный файл из каталога медиа');
define('_COM_SEF_NOACCESS','Доступ невозможен');
define('_COM_SEF_NOCACHE','не кэшировать');
define('_COM_SEF_NOLEADSLASH','В Новой ссылке (URL) недолжно быть Слэша впереди.');
define('_COM_SEF_NOREAD','ОШИБУКА: Невозможно прочитать файл ');
define('_COM_SEF_NORECORDS','Записи не найдены.');
define('_COM_SEF_OFFICIAL','Оффициальный Форум Проекта');
define('_COM_SEF_OK',' OK ');
define('_COM_SEF_OLDURL','Новая SEF ссылка');
define('_COM_SEF_PAGEREP_CHAR','<nobr>Символ разделителя страниц:</nobr>');
define('_COM_SEF_PAGETEXT','Текст страницы:');
define('_COM_SEF_PROCEED',' Продолжить ');
define('_COM_SEF_PURGE404','Очистить<br/>404 логи');
define('_COM_SEF_PURGE404DESC','Очистка лога ошибок 404');
define('_COM_SEF_PURGECUSTOM','Очистить<br/>Выборочные переадресаци');
define('_COM_SEF_PURGECUSTOMDESC','Очитска Выборочных переадресаций');
define('_COM_SEF_PURGEURL','Очистить<br/>SEF ссылки');
define('_COM_SEF_PURGEURLDESC','Очистка SEF ссылок');
define('_COM_SEF_REALURL','Реальные ссылки');
define('_COM_SEF_RECORD',' запись');
define('_COM_SEF_RECORDS',' записи');
define('_COM_SEF_REPLACE_CHAR','Символ замены:');
define('_COM_SEF_SAVEAS','Сохранить как Выборочную Переадресацию');
define('_COM_SEF_SEFURL','SEF ссылки');
define('_COM_SEF_SELECT_DELETE','Выберите элемент для удаления');
define('_COM_SEF_SELECT_FILE','Пожалуйста, выберите сначала файл');
define('_COM_SEF_SH_ACTIVATE_IJOOMLA_MAG', 'Активировать магазин iJoomla в содержимом');
define('_COM_SEF_SH_ADV_INSERT_ISO', 'Вставить код ISO');
define('_COM_SEF_SH_ADV_MANAGE_URL', 'Обработки URL');
define('_COM_SEF_SH_ADV_TRANSLATE_URL', 'Переводить URL');
define('_COM_SEF_SH_ALWAYS_INSERT_ITEMID', 'Всегда прибавлять Itemid к SEF ссылке');
define('_COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX', 'ID Меню');
define('_COM_SEF_SH_ALWAYS_INSERT_MENU_TITLE', 'Всегда добавлять заголовок меню');
define('_COM_SEF_SH_CACHE_TITLE', 'Управление Кэшированием');
define('_COM_SEF_SH_CAT_TABLE_SUFFIX', 'Tabla');
define('_COM_SEF_SH_CB_INSERT_NAME', 'Добавить название Community Builder');
define('_COM_SEF_SH_CB_INSERT_USER_ID', 'Добавить ID пользователя');
define('_COM_SEF_SH_CB_INSERT_USER_NAME', 'Добавить Имя пользователя');
define('_COM_SEF_SH_CB_NAME', 'Название CB по умолчанию:');
define('_COM_SEF_SH_CB_TITLE', 'Параметры Community Builder ');
define('_COM_SEF_SH_CB_USE_USER_PSEUDO', 'Вставить Псеводоним пользователя');
define('_COM_SEF_SH_CONF_TAB_ADVANCED', 'Расширенные');
define('_COM_SEF_SH_CONF_TAB_BY_COMPONENT', 'Компоненты');
define('_COM_SEF_SH_CONF_TAB_MAIN', 'Основные');
define('_COM_SEF_SH_CONF_TAB_PLUGINS', 'Плагины');
define('_COM_SEF_SH_DEFAULT_MENU_ITEM_NAME', 'Стандартный заголовок меню:');
define('_COM_SEF_SH_DO_NOT_INSERT_LANGUAGE_CODE','Не вставлять код');
define('_COM_SEF_SH_DO_NOT_OVERRIDE_SEF_EXT', 'Не замещать sef_ext файл');
define('_COM_SEF_SH_DO_NOT_TRANSLATE_URL','Не переводить');
define('_COM_SEF_SH_ENCODE_URL', 'Преобразовать URL');
define('_COM_SEF_SH_FB_INSERT_CATEGORY_ID', 'Добавить ID категории');
define('_COM_SEF_SH_FB_INSERT_CATEGORY_NAME', 'Вставить Название категории');
define('_COM_SEF_SH_FB_INSERT_MESSAGE_ID', 'Вставить ID сообщения');
define('_COM_SEF_SH_FB_INSERT_MESSAGE_SUBJECT', 'Вставить предмент сообщения');
define('_COM_SEF_SH_FB_INSERT_NAME', 'Вставить название Fireboard');
define('_COM_SEF_SH_FB_NAME', 'Название Fireboard по умолчанию:');
define('_COM_SEF_SH_FB_TITLE', 'Настройки Fireboard ');
define('_COM_SEF_SH_FILTER', 'Фильтр');
define('_COM_SEF_SH_FORCE_NON_SEF_HTTPS', 'Использовать non-Sef, если HTTPS');
define('_COM_SEF_SH_GUESS_HOMEPAGE_ITEMID', 'Предполагать Itemid на главной');
define('_COM_SEF_SH_IJOOMLA_MAG_NAME', 'Название магазина по умолчанию:');
define('_COM_SEF_SH_IJOOMLA_MAG_TITLE', 'Настройки Магазина iJoomla');
define('_COM_SEF_SH_INSERT_GLOBAL_ITEMID_IF_NONE', 'Вставить Itemid меню, если нет');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_ARTICLE_ID', 'Добавить ID материала в URL');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_ISSUE_ID', 'Добавить исходный ID в URL');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_MAGAZINE_ID', 'Добавить ID магазина в URL');
define('_COM_SEF_SH_INSERT_IJOOMLA_MAG_NAME', 'Добавить название магазина в URL');
define('_COM_SEF_SH_INSERT_LANGUAGE_CODE', 'Вставить Код языка в URL');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID', 'Добавить номерной ID в URL');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID_ALL_CAT', 'Все категории:');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID_CAT_LIST', 'Применить ко всем категориям');
define('_COM_SEF_SH_INSERT_NUMERICAL_ID_TITLE', 'Уникальный ID');
define('_COM_SEF_SH_INSERT_PRODUCT_ID', 'Добавить ID продукта в URL');
define('_COM_SEF_SH_INSERT_TITLE_IF_NO_ITEMID', 'Вставить заголовок меню, если нет Itemid');
define('_COM_SEF_SH_ITEMID_TITLE', 'Управление Itemid');
define('_COM_SEF_SH_LETTERMAN_DEFAULT_ITEMID', 'Itemid для страницы Letterman по умолчанию:');
define('_COM_SEF_SH_LETTERMAN_TITLE', 'Настройки Letterman ');
define('_COM_SEF_SH_LIVE_SECURE_SITE', 'URL защищенного SSL соединения:');
define('_COM_SEF_SH_LOG_404_ERRORS', 'Логи ошибок 404');
define('_COM_SEF_SH_MAX_URL_IN_CACHE', 'Размер кэш:');
define('_COM_SEF_SH_OVERRIDE_SEF_EXT', 'Заместить sef_ext файл');
define('_COM_SEF_SH_REDIR_404', '404');
define('_COM_SEF_SH_REDIR_CUSTOM', 'Выборочные');
define('_COM_SEF_SH_REDIR_SEF', 'SEF');
define('_COM_SEF_SH_REDIR_TOTAL', 'Всего');
define('_COM_SEF_SH_REDIRECT_JOOMLA_SEF_TO_SEF', '301 перенаправление с JOOMLA SEF в sh404SEF');
define('_COM_SEF_SH_REDIRECT_NON_SEF_TO_SEF', '301 перенаправление из не-Sef в SEF URL');
define('_COM_SEF_SH_REPLACEMENTS', 'Список заменяемых символов:');
define('_COM_SEF_SH_SHOP_NAME', 'Название магазина по умолчанию:');
define('_COM_SEF_SH_TRANSLATE_URL', 'Перевести ссылку');
define('_COM_SEF_SH_TRANSLATION_TITLE', 'Управление переводом');
define('_COM_SEF_SH_USE_URL_CACHE', 'Включить кэш URL');
define('_COM_SEF_SH_VM_ADDITIONAL_TEXT', 'Дополнительный текст');
define('_COM_SEF_SH_VM_DO_NOT_SHOW_CATEGORIES', 'Нет');
define('_COM_SEF_SH_VM_INSERT_CATEGORIES', 'Добавить категории');
define('_COM_SEF_SH_VM_INSERT_CATEGORY_ID', 'Вставить ID категории в URL');
define('_COM_SEF_SH_VM_INSERT_FLYPAGE', 'Всавить Имя flypage');
define('_COM_SEF_SH_VM_INSERT_MANUFACTURER_ID', 'Добавить ID производителя');
define('_COM_SEF_SH_VM_INSERT_MANUFACTURER_NAME', 'Добавить название производителя в URL');
define('_COM_SEF_SH_VM_INSERT_SHOP_NAME', 'Добавить название магазина в ссылку (URL)');
define('_COM_SEF_SH_VM_SHOW_ALL_CATEGORIES', 'Все входящие категории');
define('_COM_SEF_SH_VM_SHOW_LAST_CATEGORY', 'Только одна последняя');
define('_COM_SEF_SH_VM_TITLE', 'Параметры Virtuemart');
define('_COM_SEF_SH_VM_USE_PRODUCT_SKU', 'Использовать SKU продукта для названия');
define('_COM_SEF_SHOW_CAT', 'Показать Категорию');
define('_COM_SEF_SHOW_SECT','Показать Разделы');
define('_COM_SEF_SHOW0','Показать SEF ссылки');
define('_COM_SEF_SHOW1','Показать 404 логи');
define('_COM_SEF_SHOW2','Показать Выборочные переадресации');
define('_COM_SEF_SKIP','пропустить');
define('_COM_SEF_SORTBY','Сортировать по:');
define('_COM_SEF_STRANGE','Произошло что-то странное. Этого недолжно быть<br />');
define('_COM_SEF_STRIP_CHAR','Удаляемые символы:');
define('_COM_SEF_SUCCESSPURGE','Записи успешно очищены');
define('_COM_SEF_SUFFIX','Расширение файлов:');
define('_COM_SEF_SUPPORT','Поддержка<br/>Веб');
define('_COM_SEF_SUPPORT_404SEF','Поддержите нас');
define('_COM_SEF_SUPPORTDESC','Перейти на сайт sh404SEF (в новом окне)');
define('_COM_SEF_TITLE_ADV','Дополнительные настройки компонента');
define('_COM_SEF_TITLE_BASIC','Основная Конфигурация');
define('_COM_SEF_TITLE_CONFIG','Конфигурация sh404SEF ');
define('_COM_SEF_TITLE_MANAGER','sh404SEF Менеджер ссылок (URL)');
define('_COM_SEF_TITLE_PURGE','База Данных Очистки sh404SEF');
define('_COM_SEF_TITLE_SUPPORT','sh404SEF Поддержка');
define('_COM_SEF_TT_404PAGE','Статичная странца для 404 ошибок - Не найдено (состояние публикации не имеет значения)');
define('_COM_SEF_TT_ADDFILE','Имя файла для добавления к ссылкам, имеющим в конце символ слэша - /. Полезен для поисковых роботов, ожидающих определенный файл, но которым возвращается ошибка 404, если файла нет.');
define('_COM_SEF_TT_ADV','<b>исп. заголовок по умолчанию</b><br/>идет нормально, если расширение SEF Advanced присутствует, то оно будет использовано.<br/><b>не кэшировать</b><br/>не сохранять в БД и создавать SEF ссылки в старом стиле<br/><b>пропустить</b><br/>не создавать SEF ссылки для этого компонента<br/>');
define('_COM_SEF_TT_ADV4','Дополнительные Настройки для ');
define('_COM_SEF_TT_ENABLED','Если выбрано Нет, то будет использован стандартный SEF для Joomla/Mambo');
define('_COM_SEF_TT_FRIENDTRIM_CHAR','Символы, которые можно использовать в ссылках (URL). Введите, разделяя их символом |');
define('_COM_SEF_TT_LOWER','Преобразовать все символы в нижний регистр в ссылке (URL)','В нижнем регистре');
define('_COM_SEF_TT_NEWURL','Эта ссылка (URL) должна начинаться с index.php');
define('_COM_SEF_TT_OLDURL','Только относительное перенаправление из Joomla/Mambo каталога <i>без</i> слэш впереди');
define('_COM_SEF_TT_PAGEREP_CHAR','Символ, используемый для отделения номеров страниц от названия ссылки (URL)');
define('_COM_SEF_TT_PAGETEXT','Текст, добавляемый к ссылке для многостраничных материалов. Используется %s для вставки номера страницы в конце ссылки. Если суффикс определен, он будет добавлен в конец строки.');
define('_COM_SEF_TT_REPLACE_CHAR','Символ, используемый для замены неизвестных символов в ссылке (URL)');
define('_COM_SEF_TT_SH_ACTIVATE_IJOOMLA_MAG', 'Если <strong>Да</strong>, то ed параметр, если присутствует в компоненте com_content, будет интерпретирован как ID iJoomla магазина.');
define('_COM_SEF_TT_SH_ADV_INSERT_ISO', 'Для каждого установленного компонента и если Ваш сайт многоязыковый, выберите вставлять или нет код ISO в SEF ссылку. Например: www.monsite.com/<b>fr</b>/introduction.html. fr для Французского. Данный код не будет добавлен в ссылку языка по умолчанию.');
define('_COM_SEF_TT_SH_ADV_MANAGE_URL', 'Для каждого установленного компонента:<br /><b>заголовок по умолчанию</b><br/>обычная обработка, если присутствует компонент SEF Advanced, то он будет использован. <br/><b>не кэшировать</b><br/>не сохранять в БД and создавать SEF ссылки в старом стиле<br/><b>пропустить</b><br/>не делать SEF ссылки для данного компонента<br/>');
define('_COM_SEF_TT_SH_ADV_OVERRIDE_SEF', 'Некоторые компоненты идут со своими файлами sef_ext предназначенными для имспользования с OpenSEF или SEF Advanced. Если параметр Да - (Заместить sef_ext), то файл данного расширения не будет использован и плагин sh404SEF будет использован вместо него (при условии, что он один для этого компонента). Если Нет, тогда sef_ext файл компонента будет использован.'); 
define('_COM_SEF_TT_SH_ADV_TRANSLATE_URL', 'Для каждого установленного компонента. Выберите, если URL будет переводиться. Не даст эффекта, если сайт имеет только один язык.');
define('_COM_SEF_TT_SH_ALWAYS_INSERT_ITEMID', 'Если Да, то не-SEF Itemid (или Itemid текущго пункта меню, если установлено Нет в не-sef URL) будет добавлен к SEF ссылке. Это может быть использоано вместо постоянной вставки параметра заголовка меню, если у Вас есть несколько пунктов меню с одинаковым заголовком (как, например, один в главном меню и один в верхнем меню)');
define('_COM_SEF_TT_SH_ALWAYS_INSERT_MENU_TITLE', 'Если Да, заголовок меню соответствующий Itemid устанавленный в не-Sef URL, или текущем элементе заголовка меню, если не установлен Itemid, будет добавлен в SEF ссылку.');
define('_COM_SEF_TT_SH_CB_INSERT_NAME', 'Если <strong>Да</strong>, заголовое меню впереди основной страницы Community Builder будет добавлен ко всем CB SEF URL');
define('_COM_SEF_TT_SH_CB_INSERT_USER_ID', 'Если <strong>Да</strong>, ID пользоателя будет добавлен к его имени <strong>при условии, что предыдущией параметрв активен (Да)</strong>, в случае, если два пользователя имеют одно имя.');
define('_COM_SEF_TT_SH_CB_INSERT_USER_NAME', 'Если <strong>Да</strong>, имя пользователя будет добавлено в SEF ссылки. <strong>ВНИМАНИЕ:</strong> это может основательно увеличить размер БД и затормозить работу сайт, если у Вас много зарегистрированных пользователей. Если выбрано Нет, то будет использован ID пользователя в следующем вормате: ..../send-user-email.html?user=245');
define('_COM_SEF_TT_SH_CB_NAME', 'Когда предыдущий параметр - Да, Вы здесь Вы можете вставить текст замены в SEF URL. Имейте ввиду, что этот текст будет постояенен и не будет переведен.');
define('_COM_SEF_TT_SH_CB_USE_USER_PSEUDO', 'Если <strong>Да</strong>, то псевдоним пользователя будет добавлен к SEF ссылке вместо его настоящего имени, если Вы выбрали данную опцию выше.');
define('_COM_SEF_TT_SH_DEFAULT_MENU_ITEM_NAME', 'Когда параметр выше выбран (Да), вы можете здесь отвергнуть текст добавленный в SEF ссылку. Имейте ввиду, что этот текст будет неизменным и не будет переведен');
define('_COM_SEF_TT_SH_ENCODE_URL', 'Если Да, то URL будет преобразован так, чтобы быть совместимым с языками, имеющими не латниские символы. Преобразованный URL будет выглядеть: mysite.com/%34%56%E8%67%12.....');
define('_COM_SEF_TT_SH_FB_INSERT_CATEGORY_ID', 'Если <strong>Да</strong>, то ID категории будет добавлено к его названию, <strong>когда предыдущий параметр также выбран (Да)</strong>, к примеру, если две категрии имеют одно и тоже название.');
define('_COM_SEF_TT_SH_FB_INSERT_CATEGORY_NAME', 'Если Да, то название категории будет добавлено во все SEF ссылки сообщений');
define('_COM_SEF_TT_SH_FB_INSERT_MESSAGE_ID', 'Если <strong>Да</strong>, ID сообщения будет добавлен к предмету сообщения <strong>когда предыдущий параметр также выбран (Да)</strong>, к примеру, если два сообщения имеют одинаковое название предмета.');
define('_COM_SEF_TT_SH_FB_INSERT_MESSAGE_SUBJECT', 'Если <strong>Да</strong>, предмет сообщения будет вставлен в SEF ссылку впереди сообщения.');
define('_COM_SEF_TT_SH_FB_INSERT_NAME', 'Если <strong>Да</strong>, то пункт заголовка меню впереди основной страницы Fireboard будет присоединен ко всем Fireboard SEF ссылкам');
define('_COM_SEF_TT_SH_FB_NAME', 'Если <strong>Да<strong>, название Fireboard (как определено в заголовке пункат меню Fireboard) всегда будет добавляться к SEF ссылкам.');
define('_COM_SEF_TT_SH_FORCE_NON_SEF_HTTPS', 'If set to Yes, URL will be forced to non sef after switching to SSL mode(HTTPS). This allows operation with some shared SSL servers causing problems otherwise.');
define('_COM_SEF_TT_SH_GUESS_HOMEPAGE_ITEMID', 'If set to yes, and on homepage only, Itemid of com_content URLs will be removed and replaced by the one sh404SEF guestimates. This is useful when some content elements can be viewed on the frontpage (in blog view for instance), and also on other pages on the site.');
define('_COM_SEF_TT_SH_IJOOMLA_MAG_NAME', 'Когда предыдущий параметр - Да, Вы можете здесь заменить текст добавленный в SEF ссылку. Имейте ввиду, что текст будет постоянен и не будет переведен');
define('_COM_SEF_TT_SH_INSERT_GLOBAL_ITEMID_IF_NONE', 'Если Itemid не установлен в не-SEF URL перед его преобразованием в SEF и Вы включите данную опцию, текущий пункт меню Itemid будет добавлен к нему. Это гарантирует, что если кликнуть, ссылка приведент на туже страницу (т.е. тоже, что и отображают модули)');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_ARTICLE_ID', 'Если <strong>Да</strong>, ID материала будет добавлен к каждому заголовку материала в URL, как в: <br /> mysite.com/Joomla-magazine/<strong>56</strong>-Good-article-title.html');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_ISSUE_ID', 'Если <strong>Да</strong>, то уникальный внутренний исходный ID будет добавлен к каждому исходному названию, чтобы быть уверенным, что оно уникально.');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_MAGAZINE_ID', 'Если <strong>Да</strong>, ID магазина будет добавлен к каждому названию магазина в URL, как в: <br /> mysite.com/<strong>4</strong>-Joomla-magazine/Good-article-title.html');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_MAG_NAME', 'Если <strong>Да<strong>, то название магазина (как определено в заголовке магазина) всегда будет добавляться к SEF ссылке');
define('_COM_SEF_TT_SH_INSERT_LANGUAGE_CODE', 'Если <strong>Да</strong>, то будет вставлен ISO код языка в SEF ссылку, кроме, если язык является языком по умолчанию для сайта.');
define('_COM_SEF_TT_SH_INSERT_NUMERICAL_ID', 'Если <strong>Да</strong>, номерной ID будет добавлен к URL в целях облегчения включения в сервис, такой как Google новости. ID будет соответствовать формату: 2007041100000, где 20070411 дата создания и 00000  - внутренний уникальный ID элемента содержания. В итоге, Вам нужно установить дату создания, когда содержимое готово к публикации. Имейте ввиду, что в дальнейшем Вы не сможете ее изменить.');
define('_COM_SEF_TT_SH_INSERT_NUMERICAL_ID_CAT_LIST', 'Номерной ID будет добавлен только в SEF ссылки элеменов содержания представленных здесь. Вы можете выбрать множество категорий нажатием и удержанием клавиши CTRL перед нажатием на название категории.');
define('_COM_SEF_TT_SH_INSERT_PRODUCT_ID', 'Если Да, ID продукта будет добавлен к названию продукта в ссылке SEF<br />Например: mysite.com/3-my-very-nice-product.html.<br />Это полезно, если вы не добавляете названия категорий в URL, так как различниые продукты могут иметь схожие названия в различных категориях. Имейте ввиду, что это не продукт SKU, но лучше, чем встроенный ID продукта, который все время однозначен.');
define('_COM_SEF_TT_SH_INSERT_TITLE_IF_NO_ITEMID', 'Если Itemid не установлен в не-SEF URL перед его before преобразованием в SEF и Вы включите данную опцию, текущий элемент Заголовка меню будет добавлен в SEF ссылку. Это стоит задействовать, если параметр выше также задействован, так как это предотвратит -2, -3, -... добавление к SEF ссылке, если материал просматривается из разных мест.');
define('_COM_SEF_TT_SH_LETTERMAN_DEFAULT_ITEMID', 'Введите Itemid страницы, которая будет добавлена в ссылки Letterman (отписаться, сообщения подтверждения, ...');
define('_COM_SEF_TT_SH_LIVE_SECURE_SITE', 'Задайте это к <strong>полному URL бызы вашего сайте когда он в режиме SSL</strong>.<br />Необходимо только если Вы используете https доступ. Если нет, то по умолчанию будет httpS://normalSiteURL.<br />Пожалуйста введите полный url без слеша впереди. Например: <strong>https://www.mysite.com</strong> или <strong>https://sharedsslserveur.com/myaccount</strong>.');
define('_COM_SEF_TT_SH_LOG_404_ERRORS', 'Если <strong>Да</strong>, 404 ошибки будут записаны в БД. Это может помочь найти ошибки в ссылках сайта. Это также займет место в БД, поэтому Вы можете отключить данный параметр, когда сайт протестирован.');
define('_COM_SEF_TT_SH_MAX_URL_IN_CACHE', 'Когда кэш ссылок (URL) активирован данный параметр устанавливает его максимальный размер. Введите максимальное число ссылок (URL), которые могут быть сохранены в кэш (дополнительные ссылки будут обработаны, но не будут сохранены в кэш, отчего время загрузки страницы увеличится). Грубо говоря, каждая ссылки (URL) занимает примерно 200 байт (100 для SEF ссылки and 100 для не-SEF ссылки). Например, 5000 ссылок займут, примерно, 1 Мб на диске.');
define('_COM_SEF_TT_SH_REDIRECT_JOOMLA_SEF_TO_SEF', 'Если <strong>Да</strong>, то стандартные ссылки JOOMLA SEF будут перенаправлены командой 301 в sh404SEF эквивалент, если они есть в БД. Если их нет в БД, то они будут созданы налету.');
define('_COM_SEF_TT_SH_REDIRECT_NON_SEF_TO_SEF', 'Если <strong>Да</strong>, не-Sef URL уже присутствующие в БД будут перенаправлены в их SEF часть используя 301 - Постоянно перемещенное перенаправление. Если SE-URL не существует, оно будет создано, если кроме этого там есть POST информация в запросе страницы.');
define('_COM_SEF_TT_SH_REPLACEMENTS', 'Символы не принятые для ссылки (URL), такие как не латинские или подчеркнутые, могут быть заменены исходя из данной таблицы замены. <br />Формат xxx | yyy для каждого заменяемого символа. xxx - заменяемый символ, а yyy - новый, заменяющий символ. <br />Может быть создано множество таких правил разделенных запятыми (,). Между старым и новым символами, используйте символ разделения | <br />Учтите также, что xxx или yyy могут быть многосимвольными, например _|oe ');
define('_COM_SEF_TT_SH_SHOP_NAME', 'Если параметр выше - Да, Вы можете здесь отвергнуть текст добавленный в SEF ссылку. Имейте ввиду, что этот текст будет неизменным и не будет переведен');
define('_COM_SEF_TT_SH_TRANSLATE_URL', 'Если Да и сайт многоязычный, значения SEF ссылок будут переведены в язык посетителя сайта, как решено в Joomfish. Если Нет, ссылки будут на языке сайта. Не используйте, если сайт не многоязычный.');
define('_COM_SEF_TT_SH_USE_URL_CACHE', 'Если Да, SEF ссылки (URL) будут записаны в кэш на диск, что существенно увеличит скорость загрузки страниц. Однако это потребует использования дисковой памяти!');
define('_COM_SEF_TT_SH_VM_ADDITIONAL_TEXT', 'Если <strong>Да</strong>, то дополнительный текст будет добавлен к категориям URL. Например: ..../category-A/View-all-products.html VS ..../category-A/ .');
define('_COM_SEF_TT_SH_VM_INSERT_CATEGORIES', 'Если <strong>Нет</strong>, название категории не будет добавлено в URL впереди продукта, как в: <br /> mysite.com/joomla-cms.html<br />Если <strong>Только одна последняя</strong> - название категории, к которой относится продукт, будет добавлено в SEF ссылку, как в: <br /> mysite.com/joomla/joomla-cms.html<br />Если <strong>Все входящие категории</strong>, то будут добавлены все категории, к которым относится продукт, как в: <br /> mysite.com/software/cms/joomla/joomla-cms.html');
define('_COM_SEF_TT_SH_VM_INSERT_CATEGORY_ID', 'Если Да, ID категории будет добавлено к каждому названию категории в URL перед название продукта, как в: <br /> mysite.com/1-software/4-cms/1-joomla/joomla-cms.html');
define('_COM_SEF_TT_SH_VM_INSERT_FLYPAGE', 'Если Да, то название flypage будет добавлено в URL впереди описания продукта. Может быть неактивно, если Вы используете лишь одну flypage.');
define('_COM_SEF_TT_SH_VM_INSERT_MANUFACTURER_ID', 'Если Да, ID производителя будет добавлено перед названием производителя в SEF ссылке<br />Например: mysite.com/6-manufacturer-name/3-my-very-nice-product.html.');
define('_COM_SEF_TT_SH_VM_INSERT_MANUFACTURER_NAME', 'Если Да, название производителя, если есть, будет добавлено в SEF ссылку впереди продукта.<br />Например: mysite.com/manufacturer-name/product-name.html');
define('_COM_SEF_TT_SH_VM_INSERT_SHOP_NAME', 'Если <strong>Да<strong>, название магазина (как определено в пункте меню заголовка магазина) будет всегда добавляться к SEF ссылке');
define('_COM_SEF_TT_SH_VM_USE_PRODUCT_SKU', 'Если Да, SKU продукта, код продукта, которые Вы вводите для каждого продукта, будут использованы вместо полного названия продукта.');
define('_COM_SEF_TT_SHOW_CAT','Если Да, то категории будут исключены из ссылки');
define('_COM_SEF_TT_SHOW_SECT','Если Да, то будет добавлено название раздела в ссылку');
define('_COM_SEF_TT_STRIP_CHAR','Символы, удаляемые из ссылок (URL). Введите, разделяя их символом |');
define('_COM_SEF_TT_SUFFIX','Расширение для \\\'files\\\'. Оставьте пустым, если не будет расширений. По умолчанию - \\\'.html\\\'.');
define('_COM_SEF_TT_USE_ALIAS','Выберите Да, чтобы использовать Псевдонимы зоголовков вместо Текста заголовка в ссылке (URL)');
define('_COM_SEF_UNWRITEABLE',' <b><font color="red">Закрыта для записи</font></b>');
define('_COM_SEF_UPLOAD_OK','Файл успешно загружен');
define('_COM_SEF_URL','Ссылки');
define('_COM_SEF_URLEXIST','Ссылка (URL) уже существует в базе данных!');
define('_COM_SEF_USE_ALIAS','Использовать псевдонимы');
define('_COM_SEF_USE_DEFAULT','Исп. заголовок по умолчанию');
define('_COM_SEF_USING_DEFAULT',' <b><font color="red">Использование Значений по умолчанию</font></b>');
define('_COM_SEF_VIEW404','Просмотреть/Изменить<br/>404 логи');
define('_COM_SEF_VIEW404DESC','Просмотреть/Изменить 404 логи');
define('_COM_SEF_VIEWCUSTOM','Просмотреть/Изменить<br/>Выборочные переадресации');
define('_COM_SEF_VIEWCUSTOMDESC','Просмотреть/Изменить Выборочные переадресации');
define('_COM_SEF_VIEWMODE','Режим просмотра:');
define('_COM_SEF_VIEWURL','Просмотреть/Изменить<br/>SEF ссылки');
define('_COM_SEF_VIEWURLDESC','Просмотреть/Изменить SEF ссылки');
define('_COM_SEF_WARNDELETE','ВНИМАНИЕ!!!  Вы собираетесь удалить ');
define('_COM_SEF_WRITE_ERROR','Ошибка при записи конфигурации');
define('_COM_SEF_WRITE_FAILED','Невозможно записать загруженный файл в дерикторию медиа');
define('_COM_SEF_WRITEABLE',' <b><font color="green">Открыта для записи</font></b>');
define('_FULL_TITLE', 'Полный заголовок');
define('_PREVIEW_CLOSE','Закрыть данное окно');
define('_TITLE_ALIAS', 'Псевдоним заголовка');

// V 1.2.4.s
define('_COM_SEF_SH_DOCMAN_TITLE', 'Параметры Docman');
define('_COM_SEF_SH_DOCMAN_INSERT_NAME', 'Вставлять имя Docman');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_NAME', 'Если <strong>Да</strong>, элемент заголовка меню принадлежащий главной странице Docman будет добавлен ко всем Docman ссылкам SEF URL');
define('_COM_SEF_SH_DOCMAN_NAME', 'Имя Docman по умолчанию:');
define('_COM_SEF_TT_SH_DOCMAN_NAME', 'Когда предыдущий параметр - Да, здесь Вы можете указать отбрасываемый текст, добавленный в SEF URL. Имейте ввиду, что этот текст будет постоянен и не будет переведен.');
define('_COM_SEF_SH_DOCMAN_INSERT_DOC_ID', 'Вставлять ID документа');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_DOC_ID', 'Если <strong>Да</strong>, то ID материала будет добавляться к его имени, что необходимо, так как некоторые материалы могут иметь одинаковые названия.');
define('_COM_SEF_SH_DOCMAN_INSERT_DOC_NAME', 'Вставить Имя документа');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_DOC_NAME', 'Если <strong>Да</strong>, имя материала будет добавлено во все SEF ссылки (URL) указыающие на данный материал.');
define('_COM_SEF_SH_MYBLOG_TITLE', 'Параметры MyBlog');
define('_COM_SEF_SH_MYBLOG_INSERT_NAME', 'Вставлять имя MyBlog');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_NAME', 'Если <strong>Да</strong>, заголовое элемента меню указывающий на главную страницу MyBlog будет добавлен ко всем MyBlog SEF ссылкам (URL).');
define('_COM_SEF_SH_MYBLOG_NAME', 'Имя Myblog по умолчанию:');
define('_COM_SEF_TT_SH_MYBLOG_NAME', 'Когда предыдущий параметр - Да, здесь Вы можете указать отбрасываемый текст, добавленный в SEF URL. Имейте ввиду, что этот текст будет постоянен и не будет переведен.');
define('_COM_SEF_SH_MYBLOG_INSERT_POST_ID', 'Вставлять ID поста');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_POST_ID', 'Если <strong>Да</strong>, внутренний ID поста будет добавлен к его заголовку, что необходимо, так как некоторые посты могут иметь одинаковые заголовки.');
define('_COM_SEF_SH_MYBLOG_INSERT_TAG_ID', 'Вставлять ID тег');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_TAG_ID', 'Если <strong>Да</strong>, внутренний ID тег будет добавлен к его имени, что необходимо, так как некоторые теги идентичны или пересекаются с другими категориями имени.');
define('_COM_SEF_SH_MYBLOG_INSERT_BLOGGER_ID', 'Вставлять Blogger ID');
define('_COM_SEF_TT_SH_MYBLOG_INSERT_BLOGGER_ID', 'Если <strong>Да</strong>, внутренни ID Blogger будет добавлен к его имени, что необходимо, так как некоторые из Blogger могут иметь одинаковые имена.');
define('_COM_SEF_SH_RW_MODE_NORMAL', 'исп. .htaccess (mod_rewrite)');
define('_COM_SEF_SH_RW_MODE_INDEXPHP', 'без .htaccess (index.php)');
define('_COM_SEF_SH_RW_MODE_INDEXPHP2', 'без .htaccess (index.php?)');
define('_COM_SEF_SH_SELECT_REWRITE_MODE', 'Режим Перезаписи (Rewriting)');
define('_COM_SEF_TT_SH_SELECT_REWRITE_MODE', 'Выберите режим Перезаписи (Rewriting) для sh404SEF.<br /><strong>исп. .htaccess (mod_rewrite)</strong><br />Режим по умолчанию: у Вас должен быть файл .htacces, правильно настроен, чтобы соответствовать конфигурации сервера<br /><strong>без .htaccess (index.php)</strong><br /><strong>В РАЗРАБОТКЕ:</strong> Вам не понадобится файл .htaccess. Данный режим использует функцию PathInfo серверов Apache. К ссылкам (URL) будет добавлено /index.php/ в начале. Возможно, чтобы и IIS сервера также поддерживали данные ссылки (URL)<br /><strong>без .htaccess (index.php?)</strong><br /><strong>В РАЗРАБОТКЕ:</strong> Вам не понадобится файл .htaccess. Этот режим идентичен предыдущему, кроме того, что используется /index.php?/ вместо /index.php/. И снова, сервера IIS могут поддерживать данные ссылки (URL)<br />');
define('_COM_SEF_SH_RECORD_DUPLICATES', 'Сохранять дубликаты ссылок (URL)');
define('_COM_SEF_TT_SH_RECORD_DUPLICATES', 'Если <strong>Да</strong>, sh404SEF будет записывать в БД все не-Sef ссылки (URL), которые добавляют аналогичные SEF ссылки. Это позволит Вам выбирать ту, что предпочтительнее, используя функцию Менеджера Дубликатов в списке SEF ссылок (URL).');
define('_COM_SEF_META_TITLE', 'Тег Заголовка (title)');
define('_COM_SEF_TT_META_TITLE', 'Введите текст, который будет вставлен в тег <strong>META Title</strong> для текущей выбранной ссылки (URL).');
define('_COM_SEF_META_DESC', 'Тег Описания (description)');
define('_COM_SEF_TT_META_DESC', 'Введите текст, который будет вставлен в тег <strong>META Description</strong> для текущей выбранной ссылки (URL).');
define('_COM_SEF_META_KEYWORDS', 'Тег Ключевых слов (keywords)');
define('_COM_SEF_TT_META_KEYWORDS', 'Введите текст, который будет вставлен в тег <strong>META Keywords</strong> для текущей выбранной ссылки (URL). Каждое слова или группа слова должны быть разделены запятыми.');
define('_COM_SEF_META_ROBOTS', 'Тег роботов (robots)');
define('_COM_SEF_TT_META_ROBOTS', 'Введите текст, который бедут вставлен в тег <strong>META Robots</strong> для текущей выбранной ссылки (URL). Данный тег сообщает поисковым машинам куда они должны проследовать по ссылкам на текущей странице и что делать с содержанием на текущей странице. Общие параметры:<br /><strong>INDEX,FOLLOW</strong> : index - проиндексировать содержание текущей страницы и проследовать по всем ссылкам найденным на странице<br /><strong>INDEX,NO FOLLOW</strong> : index - проиндексировать текущую страницу, но не идти по ссылками найденным на странице<br /><strong>NO INDEX, NO FOLLOW</strong> : не индексировать текущую страницу и не идти по ссылкам найденным на ней.<br />');
define('_COM_SEF_META_LANG', 'Тег Языка (language)');
define('_COM_SEF_TT_META_LANG', 'Введите текст, который будет вставлен в тег <strong>META http-equiv= Content-Language</strong> для текущей выбранной ссылки (URL). ');
define('_COM_SEF_SH_CONF_TAB_META', 'Meta/SEO');
define('_COM_SEF_SH_CONF_META_DOC', 'sh404SEF имеет несколько плагинов для <strong>автоматического</strong> создания META тегов для некоторых компонентов. Не создавайте их вручную пока автоматически создаваемые не перестануть Вас удовлетворять!<br>');
define('_COM_SEF_SH_REMOVE_JOOMLA_GENERATOR', 'Убрать тег Joomla Generator');
define('_COM_SEF_TT_SH_REMOVE_JOOMLA_GENERATOR', 'Если <strong>Да</strong>, мета тег Generator = Joomla будет убираться со всех страниц.');
define('_COM_SEF_SH_PUT_H1_TAG', 'Вставлять теги h1');
define('_COM_SEF_TT_SH_PUT_H1_TAG', 'Если <strong>Да</strong>, обычные заголовки содержимого будут помещаться между тегов h1. Эти заголовки в основном размещаются в CSS классе Joomla, название которого начинается с <strong>contentheading</strong>.');
define('_COM_SEF_SH_META_MANAGEMENT_ACTIVATED', 'Активировать Meta менеджмент');
define('_COM_SEF_TT_SH_META_MANAGEMENT_ACTIVATED', 'Если <strong>Да</strong>, то META теги Title, Description, Keywords, Robots и Language будут управляться sh404SEF (и его модулем shCustomTags). Иными словами, оригинальные значения созданные Joomla и/или другими компонентами будут оставлены без изменений. ');
define('_COM_SEF_TITLE_META_MANAGEMENT', 'Менеджмент Meta тегов');
define('_COM_SEF_META_EDIT', 'Изменить теги');
define('_COM_SEF_META_ADD', 'Добавить теги');
define('_COM_SEF_META_TAGS', 'META теги');
define('_COM_SEF_META_TAGS_DESC', 'Создать/изменить Meta теги');
define('_COM_SEF_PURGE_META_DESC', 'Удалить Meta теги');
define('_COM_SEF_PURGE_META', 'Очистить META');
define('_COM_SEF_IMPORT_EXPORT_META', 'Импорт/ экспорт META');
define('_COM_SEF_NEW_META', 'Новый META');
define('_COM_SEF_NEWURL_META', 'Не-SEF ссылка');
define('_COM_SEF_TT_NEWURL_META', 'Введите не-Sef ссылку (URL), для который Вы хотите установить Meta теги. ВНИМАНИЕ: она должна с <strong>index.php</strong>!');
define('_COM_SEF_BAD_META', 'Пожалуйста, проверьте информацию: что-то из введенного неверно.');
define('_COM_SEF_META_TITLE_PURGE', 'Стереть Meta теги');
define('_COM_SEF_META_SUCCESS_PURGE', 'Meta теги успешно стерты');
define('_COM_SEF_IMPORT_META', 'Импорт Meta тегов');
define('_COM_SEF_EXPORT_META', 'Экспорт Meta тегов');
define('_COM_SEF_IMPORT_META_OK', 'Meta теги успешно импортированы');
define('_COM_SEF_SELECT_ONE_URL', 'Пожалуйста, выберите одну (и только одну) ссылку (URL).');
define('_COM_SEF_MANAGE_DUPLICATES', 'URL менеджмент для: ');
define('_COM_SEF_MANAGE_DUPLICATES_RANK', 'Класс');
define('_COM_SEF_MANAGE_DUPLICATES_BUTTON', 'Дубликат ссылки');
define('_COM_SEF_MANAGE_MAKE_MAIN_URL', 'Главная ссылка (URL)');
define('_COM_SEF_BAD_DUPLICATES_DATA', 'Ошибка: неверные данные ссылки (URL)');
define('_COM_SEF_BAD_DUPLICATES_NOTHING_TO_DO', 'Данная ссылка (URL) уже является главной');
define('_COM_SEF_MAKE_MAIN_URL_OK', 'Операция успешно завершена');
define('_COM_SEF_MAKE_MAIN_URL_ERROR', 'Произошла ошибка, действие прекращено');
define('_COM_SEF_SH_CONTENT_TITLE', 'Параметры Содержания');
define('_COM_SEF_SH_INSERT_CONTENT_TABLE_NAME', 'Вставить имя таблицы содержания');
define('_COM_SEF_TT_SH_INSERT_CONTENT_TABLE_NAME', 'Если <strong>Да</strong>, заголовок элемента меню впереди таблицы материалов (категории или раздела), который будет добавлен к его SEF ссылке. Это позволяет разделять отображение таблицы из блога.');
define('_COM_SEF_SH_CONTENT_TABLE_NAME', 'Имена ссылок таблицы по умолчанию:');
define('_COM_SEF_TT_SH_CONTENT_TABLE_NAME', 'Когда предыдущий параметр - Да, здесь вы можете добавить текст замещения в SEF ссылке (URL). Имейте в виду, что данный текст будет поятоянен и не будет переведен.');
define('_COM_SEF_SH_REDIRECT_WWW', '301 перенаправление www/не-www');
define('_COM_SEF_TT_SH_REDIRECT_WWW', 'Если Да, то sh404SEF будет выполнять 301 перенаправление, если название сайта запрошено без www. Если URL сайта начинается с www, или если сайт запросшен с www, в то время, как его название без www - это предотвратит дублирование содержания и некоторые проблемы, зависящие от конфигурации вашего сервера Apache, такие как проблемы с WYSYWIG редакторами в Joomla');
define('_COM_SEF_SH_INSERT_PRODUCT_NAME', 'Вставить название продукта');
define('_COM_SEF_TT_SH_INSERT_PRODUCT_NAME', 'Если Да, то название продукта будет вставлено в URL');
define('_COM_SEF_SH_VM_USE_PRODUCT_SKU_124S', 'Вставить код продукта');
define('_COM_SEF_TT_SH_VM_USE_PRODUCT_SKU_124S', 'Если Да, то код продукта (названный SKU в Virtuemart) будет добавлен в URL.');

// V 1.2.4.t
define('_COM_SEF_SH_DOCMAN_INSERT_CAT_ID', 'Вставить ID категории');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_CAT_ID', 'Если <strong>Да</strong>, то ID категории будет добавлен к ее названию, <strong>когда предыдущий параметр также установлен в Да</strong>, в случае если две категории имеют одинаковое название.');
define('_COM_SEF_SH_DOCMAN_INSERT_CATEGORIES', 'Вставить название категории?');
define('_COM_SEF_TT_SH_DOCMAN_INSERT_CATEGORIES', 'Если <strong>Нет</strong>, название категории не будет вставлено в URL, как в: <br /> mysite.com/joomla-cms.html<br />Если <strong>Только один последний</strong>, название категории будет добавлено в SEF URL, как в: <br /> mysite.com/joomla/joomla-cms.html<br />Если <strong>Все группы категорий</strong>, то будут добавлены названия всех категорий, как в: <br /> mysite.com/software/cms/joomla/joomla-cms.html');
define('_COM_SEF_SH_FORCED_HOMEPAGE', 'URL главной страницы:');
define('_COM_SEF_TT_SH_FORCED_HOMEPAGE', 'Здесь вы можете добавить URL главной страницы принудительно. Необходимо, если вы установили `splash page` обычно как файл index.html, который отображается, когды вы вводите адрес, например, www.mysite.com. Если так, введите URL так: www.mysite.com/index.php (без / на конце), также как отображался сайт Joomla, когда выбиралась главная страница в главной меню или пути.');
define('_COM_SEF_SH_INSERT_CONTENT_BLOG_NAME', 'Вставить название отображаемого блога');
define('_COM_SEF_TT_SH_INSERT_CONTENT_BLOG_NAME', 'Если <strong>Да</strong>, заголовок пункта меню, относящегося к блогу (категории или раздела) будет добавлен к его SEF URL. Это позволяет разделить отображаемые таблицы от отображаемых блогов.');
define('_COM_SEF_SH_CONTENT_BLOG_NAME', 'Название отображаемого блога по умолчанию:');
define('_COM_SEF_TT_SH_CONTENT_BLOG_NAME', 'Когда предыдущий параметр Да, вы можете отвергнуть текст добавленный здесь в SEF URL. Имейте ввиду, что данный текст будет постоянен и не будет переведен.');
define('_COM_SEF_SH_MTREE_TITLE', 'Параметры Mosets Tree');
define('_COM_SEF_SH_MTREE_INSERT_NAME', 'Вставить название MTree');
define('_COM_SEF_TT_SH_MTREE_INSERT_NAME', 'Если <strong>Да</strong>, заголовок пункта меню принадлежащий Mosets Tree будет добавлен к его SEF URL.');
define('_COM_SEF_SH_MTREE_NAME', 'Название MTree по умолчанию:');
define('_COM_SEF_SH_MTREE_INSERT_LISTING_ID', 'Вставить ID списка');
define('_COM_SEF_TT_SH_MTREE_INSERT_LISTING_ID', 'Если <strong>Да</strong>, ID списка будет добавлен к его названию, в случае, если два списка имеют одинаковые названия.');
define('_COM_SEF_SH_MTREE_PREPEND_LISTING_ID', 'Добавить ID к названию');
define('_COM_SEF_TT_SH_MTREE_PREPEND_LISTING_ID', 'Если <strong>Да</strong>, когда предыдущий параметр также Да, то ID будет <strong>присоединено</strong> к названию списка. Если Нет, то оно будет <strong>добавлено</strong>.');
define('_COM_SEF_SH_MTREE_INSERT_LISTING_NAME', 'Добавить название списка');
define('_COM_SEF_TT_SH_MTREE_INSERT_LISTING_NAME', 'Если <strong>Да</strong>, то название списка будет добавлено во все URL относящиеся к данному списку.');

define('_COM_SEF_SH_IJOOMLA_NEWSP_TITLE', 'Параметры News Portal');
define('_COM_SEF_SH_INSERT_IJOOMLA_NEWSP_NAME', 'Вставить название News Portal');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_NEWSP_NAME', 'Если <strong>Да</strong>, элемент меню заголовка, относящийся к iJoomla News Portal будет добавлен к его SEF URL.');
define('_COM_SEF_SH_IJOOMLA_NEWSP_NAME', 'Название News Portal по умолчанию:');
define('_COM_SEF_SH_INSERT_IJOOMLA_NEWSP_CAT_ID', 'Вставить ID категории');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_NEWSP_CAT_ID', 'Если <strong>Да</strong>, ID категории будет добавлен к ее названию, в случае, если два списка имеют одинаковое название.');
define('_COM_SEF_SH_INSERT_IJOOMLA_NEWSP_SECTION_ID', 'Вставить ID раздела');
define('_COM_SEF_TT_SH_INSERT_IJOOMLA_NEWSP_SECTION_ID', 'Если <strong>Да</strong>, ID раздела будет добавлен к его названию, в случае, если два списка имеют одинаковое название.');
define('_COM_SEF_SH_REMO_TITLE', 'Параметры Remository');
define('_COM_SEF_SH_REMO_INSERT_NAME', 'Вставить название Remository');
define('_COM_SEF_TT_SH_REMO_INSERT_NAME', 'Если <strong>Да</strong>, то элемент заголовка меню относящийся к Remository будет добавлен к его SEF URL.');
define('_COM_SEF_SH_REMO_NAME', 'Название Remository по умолчанию');

define('_COM_SEF_SH_CB_SHORT_USER_URL', 'Короткий URL к профилю пользователя');
define('_COM_SEF_TT_SH_CB_SHORT_USER_URL', 'Если <strong>Да</strong>, то пользователь сможет обратиться к своему профилю через короткий URL, похожий на www.mysite.com/имя пользователя. Перед включением данной опции убедитесь, что это не создаст проблем с существующим URL на сайте.');

define('_COM_SEF_NEW_HOME_META', 'Meta Домашней страницы');
define('_COM_SEF_CONF_ERASE_HOME_META', 'Вы действительно хотите удалить заголовое домашней страницы и мета-теги?');
define('_COM_SEF_SH_UPGRADE_TITLE', 'Обновить конфигурацию');
define('_COM_SEF_SH_UPGRADE_KEEP_URL', 'Сохранить автоматические URL');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_URL', 'Если <strong>Да</strong>, SEF URL автоматически созданные sh40SEF будут записаны и сохранены, когда вы деинсталлируете компонент. Таким образом, вы сможете вернуть их, когда установите новую версию, буз необходимости в дополнительных действиях.');
define('_COM_SEF_SH_UPGRADE_KEEP_CUSTOM', 'Сохранить выборочные URL');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_CUSTOM', 'Если <strong>Да</strong>, то выборочные SEF URL, которые были введены, будут записаны и сохранены, когда вы деинсталлируете компонент. Таким образом, вы сможете вернуть их, когда установите новую версию, буз необходимости в дополнительных действиях.');
define('_COM_SEF_SH_UPGRADE_KEEP_META', 'Сохранить Title и Meta');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_META', 'Если <strong>Да</strong>, то выборочные Title и Meta теги, которые были введены, будут записаны и сохранены, когда вы деинсталлируете компонент. Таким образом, вы сможете вернуть их, когда установите новую версию, буз необходимости в дополнительных действиях.');
define('_COM_SEF_SH_UPGRADE_KEEP_MODULES', 'Сохранить параметры модулей');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_MODULES', 'Если <strong>Да</strong>, то текущие параметры публикации такие как: позиция, порядок, заголовки и т.д. из shJoomfish и shCustomtags модулей  которые были введены, будут записаны и сохранены, когда вы деинсталлируете компонент. Таким образом, вы сможете вернуть их, когда установите новую версию, буз необходимости в дополнительных действиях.');
define('_COM_SEF_IMPORT_OPEN_SEF','Импортировать перенаправления из OpenSEF');
define('_COM_SEF_IMPORT_ALL','Импорт перенаправлений');
define('_COM_SEF_EXPORT_ALL','Экспорт перенаправлений');
define('_COM_SEF_IMPORT_EXPORT_CUSTOM','Импорт/Экспорт выборочных перенаправлений');
define('_COM_SEF_DUPLICATE_NOT_ALLOWED', 'Данный URL уже существует, в то время, как вы не разрешаете дубликаты URL');
define('_COM_SEF_SH_INSERT_CONTENT_MULTIPAGES_TITLE', 'Активировать умные заголовки многостраничных материалов');
define('_COM_SEF_TT_SH_INSERT_CONTENT_MULTIPAGES_TITLE', 'Если Да, то для многостраничных материалов (тех, что с оглавлением), sh404SEF будет использовать заголовки страниц, вставленных с использованием команды mospagebreak: {mospagebreak title=Заголовок_Следующей_Страницы & heading=Заголовое_Предыдущей_Страницы}, вместо номера страницы<br />Например SEF URL похожий на www.mysite.com/user-documentation/<strong>Page-2</strong>.html будет заменен на www.mysite.com/user-documentation/<strong>Getting-started-with-sh404SEF</strong>.html.');

// v x
define('_COM_SEF_SH_UPGRADE_KEEP_CONFIG', 'Сохранение конфигурации');
define('_COM_SEF_TT_SH_UPGRADE_KEEP_CONFIG', 'Если Да, то все конфигурационные параметры будут записаны и сохранены, когда вы деинсталлируете компонент. Таким образом вы сможете использовать старые настройки, когда ставите новую версию.');
define('_COM_SEF_SH_CONF_TAB_SECURITY', 'Безопасность');
define('_COM_SEF_SH_SECURITY_TITLE', 'Параметры безопасности');
define('_COM_SEF_SH_HONEYPOT_TITLE', 'Параметры Проекта Honey Pot');
define('_COM_SEF_SH_CONF_HONEYPOT_DOC', 'Проект Honey Pot - это инициатива, направленная на защиту web сайтов от спам-ботов. Она предусматривает БД для проверки IP адреса посетителя защищая от известных роботов. Использование данной БД требует ключ доступа (бесплатно), который можно получить на <a href="http://www.projecthoneypot.org/httpbl_configure.php"> с сайта проекта</a><br />(Сначала вам необходимо зарегистрироваться перед запросом вашего ключа доступа - это также бесплатно). Если можете, примите участие в помощи проекту установкой `ловушки` у себя на сайте, в помощь идентификации спам роботов.');
define('_COM_SEF_SH_ACTIVATE_SECURITY', 'Вкл. функции безопасности');
define('_COM_SEF_TT_SH_ACTIVATE_SECURITY', 'Если Да, sh404SEF будет производить некоторые основные проверки запросов URL на вашем сайте в целях защиты от распространенных атак.');
define('_COM_SEF_SH_LOG_ATTACKS', 'Вести Логи атак');
define('_COM_SEF_TT_SH_LOG_ATTACKS', 'Если Да, идентифицированные атаки будут записаны в текстовый файл, который будет включать IP адрес атакующего и сделанный запрос страницы.<br />На один месяц один файл. Располагаются они в <root сайта>/administrator/com_sh404sef/logs directory. Скачать их можно используя FTP или используя утилиту Joomla, например, как Joomla Explorer для их просмотра. В файле текст разделен TAB`ами, чтобы ваш текстовый процессор смог легче его открыть.');	            
define('_COM_SEF_SH_CHECK_HONEY_POT', 'Использовать Проект Honey Pot');
define('_COM_SEF_TT_SH_CHECK_HONEY_POT', 'Если Да, то IP адрес ваших посетителей будет проверен по БД Проекта Hoeny Pot, используя их HTTP:BL сервис. Это бесплатно, но требуется получить ключ доступа с их сайта.');
define('_COM_SEF_SH_HONEYPOT_KEY', 'Ключ доступа Проекта Honey Pot');
define('_COM_SEF_TT_SH_HONEYPOT_KEY', 'Если использование Проекта Honey Pot активировано, вам необходимо получить ключ достпа от P.H.P. Тип полученного ключа здесь же. Это 12-ти символьная строка.');	             
define('_COM_SEF_SH_HONEYPOT_ENTRANCE_TEXT', 'Текст альтернативного входа');
define('_COM_SEF_TT_SH_HONEYPOT_ENTRANCE_TEXT', 'Если IP адрес посетителя был обозначен, как подозрительный Проектом Honey Pot, то доступ будет запрещен (403 код ошибки). <br />Однако, на случай ошибочного определения, текст, введенный здесь, будет отображен посетителю со ссылкой, на которую ему/ей надо кликнуть, чтобы попасть на сайт. Лишь человек сможет прочитать и понять этот текст, а робот нет. <br />Вобщем, вы можете ввести текст для вашей ссылки.' );	             
define('_COM_SEF_SH_SMELLYPOT_TEXT', 'Текст ловушка для робота');
define('_COM_SEF_TT_SH_SMELLYPOT_TEXT', 'Когда спамерский робот определен с помощью Проекта Honey Pot и доступ был запрещен, добавляется ссылка внизу запрещающей доступ страницы в целях записи действий робота Проектом Honey Pot. Также добавляется сообщение для предотвращения нажатия на ссылку человеком, чтобы не было ложных занесений в список. ');
define('_COM_SEF_SH_ONLY_NUM_VARS', 'Числовые параметры');
define('_COM_SEF_TT_SH_ONLY_NUM_VARS', 'Названия параметров, помещенные в данный список, будут проверяться, чтобы они были лишь числовыми: только цифры от 0 по 9. Вводить по одному параметру на строку.');
define('_COM_SEF_SH_ONLY_ALPHA_NUM_VARS', 'Альфа-числовые параметры');
define('_COM_SEF_TT_SH_ONLY_ALPHA_NUM_VARS', 'Названия параметров, помещенные в данный список, будут проверяться, чтобы они были лишь альфа-числовыми: цифры от 0 по 9 и буквы от a по z. Вводить по одному параметру на строку.');
define('_COM_SEF_SH_NO_PROTOCOL_VARS', 'Проверять гиперссылки в параметрах');
define('_COM_SEF_TT_SH_NO_PROTOCOL_VARS', 'Название параметров, помещенные в данный список, будут проверяться на отсутствие в них гиперссылок, начинающихся с http://, https://, ftp:// ');
define('_COM_SEF_SH_IP_WHITE_LIST', 'Белый IP лист');
define('_COM_SEF_TT_SH_IP_WHITE_LIST', 'Любой запрос страницы, приходящий с IP адреса из данного списка, будет <stong>принят</strong>, с условием прохождения URL выше упоминаемых проверок. Вводить по одному IP на строку.<br />Можно использовать * как группвой символ, как в: 192.168.0.*. Это включит IP адреса с 192.168.0.1 по 192.168.0.255.');
define('_COM_SEF_SH_IP_BLACK_LIST', 'Блэк IP лист');
define('_COM_SEF_TT_SH_IP_BLACK_LIST', 'Любой запрос страницы, приходящий с IP адреса из данного списка, будет <strong>отклонен</strong>, с условием прохождения URL выше упоминаемых проверок. Вводить по одному IP на строку.<br />Можно использовать * как групповой символ, как в: 192.168.0.*. Это ключит IP адреса с 192.168.0.1 по 192.168.0.255.');
define('_COM_SEF_SH_UAGENT_WHITE_LIST', 'Белый лист UserAgent`а');
define('_COM_SEF_TT_SH_UAGENT_WHITE_LIST', 'Любой запрос, сделанный UserAgent строкой из этого списка, будет <stong>принят</strong>, с условием прохождения URL выше упоминаемых проверок. Вводить по одной UserAgent строке на линию (строку).');
define('_COM_SEF_SH_UAGENT_BLACK_LIST', 'Блэк лист UserAgent`а');
define('_COM_SEF_TT_SH_UAGENT_BLACK_LIST', 'Любой запрос, сделанный UserAgent строкой из этого списка, будет <strong>отклонен</strong>, с условием прохождения URL выше упоминаемых проверок. Вводить по одному IP на строку.');
define('_COM_SEF_SH_MONTHS_TO_KEEP_LOGS', 'Сколько месяцев хранить логи безопасности?');
define('_COM_SEF_TT_SH_MONTHS_TO_KEEP_LOGS', 'Если логирование атак активно, то здесь можно задать количество месяцев хранения лог файлов. Например, задание 1 означает, что данный месяц ПЛЮС месяц до будут сохранены. Логи же за пердыдущие месяца будут удалены.');
define('_COM_SEF_SH_ANTIFLOOD_TITLE', 'Параметры анти-флуда');
define('_COM_SEF_SH_ACTIVATE_ANTIFLOOD', 'Активировать анти-флуд');
define('_COM_SEF_TT_SH_ACTIVATE_ANTIFLOOD', 'Если Да, то sh404SEF будет проверять, чтобы любой IP адрес не смог делать слишком много запросов к сайту. Делая множественные запросы, хакер может сделать ваш сайт недоступным путем повышенной нагрузки.');
define('_COM_SEF_SH_ANTIFLOOD_ONLY_ON_POST', 'Только если POST данные (формы)');
define('_COM_SEF_TT_SH_ANTIFLOOD_ONLY_ON_POST', 'Если Да, то данный контроль проявится, если есть какие-либо POST данные в запросе страницы. Обычно это проявляется на страницах форм. Таким образом вы можете ограничиться анти-флуд контролем только к формам, чтобы оградить сайт от роботов создающих комменты.');
define('_COM_SEF_SH_ANTIFLOOD_PERIOD', 'Анти-флуд контроль');
define('_COM_SEF_TT_SH_ANTIFLOOD_PERIOD', 'Время (в секундах), сверх которго будут контролироваться запросы с одного и того же IP адреса.');
define('_COM_SEF_SH_ANTIFLOOD_COUNT', 'Максимальное число запросов');
define('_COM_SEF_TT_SH_ANTIFLOOD_COUNT', 'Число запросов, при котором сработает блокировка страниц для нарушающего IP адреса. Например, введение периода = 10 и кол-ва запросов = 4 заблокирует доступ (возвращение кода ошибки 403 и пустой страницы) как только 4 запроса будут приняты с нарушающего IP адреса менее чем за 10 секунд. Конечно же, доступ будет заблокирован только для этого IP адреса, а не для всех ваших посетителей.');
define('_COM_SEF_SH_CONF_TAB_LANGUAGES', 'Языки');
define('_COM_SEF_SH_DEFAULT', 'По умолчанию');
define('_COM_SEF_SH_YES', 'Да');
define('_COM_SEF_SH_NO', 'Нет');
define('_COM_SEF_TT_SH_INSERT_LANGUAGE_CODE_PER_LANG', 'Если Да, код языка будет добавлен в URL для <strong>данного языка</strong>. Если Нет, то код языка никогда не будет добавляться. Если по умолчанию, то код языка будет добавляться для всех языков, но в языке по умолчанию данного сайта.');
define('_COM_SEF_TT_SH_TRANSLATE_URL_PER_LANG', 'Если Да, и ваш сайт мультиязычный, то ваш URL будет переведен для URL <strong>в данном языке</strong>, согласно настройкам Joomfish. Если Нет, URL никогда не будет переводитсья. Если по умолчанию, они также будут переведены. Не дает эффекта на одноязычных сайтах.');
define('_COM_SEF_TT_SH_INSERT_LANGUAGE_CODE_GEN', 'Если Да, то код языка будет добавлен в URL сделанный sh404SEF. Вы также можете иметь и поязыковые настройки (см. ниже).');
define('_COM_SEF_TT_SH_TRANSLATE_URL_GEN', 'Если Да, и ваш сайт мультиязычный, URL будет переведен на язык вашего посетителя, согласно настройкам Joomfish. Иначе URL останется в языке по умолчанию сайта. Вы также можете иметь и поязыковые настройки (см. ниже).');
define('_COM_SEF_SH_ADV_COMP_DEFAULT_STRING', 'Имя по умолчанию');
define('_COM_SEF_TT_SH_ADV_COMP_DEFAULT_STRING', 'Если вы введете здесь текстовую строку, то она будет добавлена в начало всех URL для этого компонента. В основном используется только для обратной совместимости со старыми URL из других SEF компонентов.');
define('_COM_SEF_TT_SH_NAME_BY_COMP', '. <br />В имени вы можете ввести то, что будет использвано вместо имени элемента меню. Чтобы это проделать, пожалуйста, обратитесь к табу <strong>Компоненты</strong>. Имейте ввиду, что этот текст будет постоянен и не будет переведен.');
define('_COM_SEF_STANDARD_ADMIN', 'Click here to switch to standard display (with only main parameters)');
define('_COM_SEF_ADVANCED_ADMIN', 'Click here to switch to extended display (with all available parameters)');
define('_COM_SEF_SH_MULTIPLE_H1_TO_H2', 'Change multiple h1 in h2');
define('_COM_SEF_TT_SH_MULTIPLE_H1_TO_H2', 'If set to Yes, and there are several instances of h1 tags on a page, they wil lbe turned into h2 tags.<br />If there is only one h1 tag on a page, it will left untouched.');
define('_COM_SEF_SH_INSERT_NOFOLLOW_PDF_PRINT', 'Insert nofollow tag on Print and PDF links');
define('_COM_SEF_TT_SH_INSERT_NOFOLLOW_PDF_PRINT', 'If set to Yes, rel=nofollow attributes will be added to all PDF and Print links created by Joomla. This reduce duplicate content seen by search engines.');
define('_COM_SEF_SH_INSERT_READMORE_PAGE_TITLE', 'Insert title in Read more ... links');
define('_COM_SEF_TT_SH_INSERT_READMORE_PAGE_TITLE', 'If set to Yes, and a Read more link is displayed on a page, the corresponding content title will be inserted in the link, to improve the link weight in search engines');
define('_COM_SEF_VM_USE_ITEMS_PER_PAGE', 'Using Items per page drop-down list');
define('_COM_SEF_TT_VM_USE_ITEMS_PER_PAGE', 'If set to Yes, URLs will be adjusted to allow for using drop-down lists to let user select the number of items per page. If you don&rsquo;t use such drop-down lists, AND your URLs are already indexed by search engines, you can set it to NO to keep your existing URL. ');
define('_COM_SEF_SH_CHECK_POST_DATA', 'Check also forms data (POST)');
define('_COM_SEF_TT_SH_CHECK_POST_DATA', 'If set to Yes, data coming from input forms will be checked against passing config variables or similar threats. This may cause unneeded blockages if you have, for instance, a forum where users may discuss such things as Joomla programming or similar. They may then want to discuss the exact text strings we are looking for as a potential attack. You should then disable this feature if you experience unappropriate forbidden access');
define('_COM_SEF_SH_SEC_STATS_TITLE', 'Security stats');
define('_COM_SEF_SH_SEC_STATS_UPDATE', 'Update');
define('_COM_SEF_SH_TOTAL_ATTACKS', 'Attacks count');
define('_COM_SEF_SH_TOTAL_CONFIG_VARS', 'mosConfig var in URL');
define('_COM_SEF_SH_TOTAL_BASE64', 'Base64 injection');
define('_COM_SEF_SH_TOTAL_SCRIPTS', 'Script injection');
define('_COM_SEF_SH_TOTAL_STANDARD_VARS', 'Illegal standard vars');
define('_COM_SEF_SH_TOTAL_IMG_TXT_CMD', 'remote file inclusion');
define('_COM_SEF_SH_TOTAL_IP_DENIED', 'IP address denied');
define('_COM_SEF_SH_TOTAL_USER_AGENT_DENIED', 'User agent denied');
define('_COM_SEF_SH_TOTAL_FLOODING', 'Too many requests (flooding)');
define('_COM_SEF_SH_TOTAL_PHP', 'Rejected by Project Honey Pot');
define('_COM_SEF_SH_TOTAL_PER_HOUR', ' /h');
define('_COM_SEF_SH_SEC_DEACTIVATED', 'Sec. functions not in use');
define('_COM_SEF_SH_TOTAL_PHP_USER_CLICKED', 'PHP, but user clicked');
define('_COM_SEF_SH_COM_SMF_TITLE', 'SMF bridge');
define('_COM_SEF_SH_INSERT_SMF_NAME', 'Insert forum name');
define('_COM_SEF_TT_SH_INSERT_SMF_NAME', 'If set to <strong>Yes</strong>, the menu element title leading to the forum main page will be prepended to all forum SEF URL');
define('_COM_SEF_SH_SMF_ITEMS_PER_PAGE', 'Items per page');
define('_COM_SEF_TT_SH_SMF_ITEMS_PER_PAGE', 'Number of items displayed on a single page of forum');
define('_COM_SEF_SH_INSERT_SMF_BOARD_ID', 'Insert forum id');
define('_COM_SEF_TT_SH_INSERT_SMF_BOARD_ID', _COM_SEF_TT_SH_FB_INSERT_CATEGORY_NAME);
define('_COM_SEF_SH_INSERT_SMF_TOPIC_ID', 'Insert topic id');
define('_COM_SEF_TT_SH_INSERT_SMF_TOPIC_ID', _COM_SEF_TT_SH_FB_INSERT_MESSAGE_ID);
define('_COM_SEF_SH_INSERT_SMF_USER_NAME', 'Insert user name');
define('_COM_SEF_TT_SH_INSERT_SMF_USER_NAME', 'If set to <strong>Yes</strong>, a user name will be inserted in each URL instead of if its id. This uses space in the DB, as a unique URl is created for each user and each function (view profile, pm, etc)');
define('_COM_SEF_SH_INSERT_SMF_USER_ID', 'Insert user id');
define('_COM_SEF_TT_SH_INSERT_SMF_USER_ID', 'If set to <strong>Yes</strong>, a user name will always be prepended with its internal id, making sure it is unique');
define('_COM_SEF_SH_PREPEND_TO_PAGE_TITLE', 'Insert before page title');
define('_COM_SEF_TT_SH_PREPEND_TO_PAGE_TITLE', 'Any text entered her will be prepended to all page title tags.');
define('_COM_SEF_SH_APPEND_TO_PAGE_TITLE', 'Append to page title');
define('_COM_SEF_TT_SH_APPEND_TO_PAGE_TITLE', 'Any text entered here will be appended to all page title tags.');
define('_COM_SEF_SH_DEBUG_TO_LOG_FILE', 'Log debug info to file');
define('_COM_SEF_TT_SH_DEBUG_TO_LOG_FILE', 'If set to Yes, sh404SEF will log to a text file many internal information. This data will help us troubleshoot problems you may be facing using sh404SEF. <br/>Warning: this file can quickly become fairly big. Also, this function will certainly slow down your site. Be sure to turn it on only when required. For this reason, it will de-activate automaticaly one hour after being started. Just turn it off then on again to activate it again. The log file is located in /administrator/components/com_sh404sef/logs/ ');

define('_COM_SEF_ALIAS_LIST', 'Alias list');
define('_COM_SEF_TT_ALIAS_LIST', 'Enter here a list of alias for this URL. Put only one alias per line, like :<br/>old-url.html<br/>or<br/>my-other-old-url.php?var=12&test=15<br>sh404SEF will do a 301 redirect to the current SEF URL if any one of those aliases is requested.');
define('_COM_SEF_HOME_ALIAS', 'Home page alias');
define('_COM_SEF_TT_HOME_PAGE_ALIAS_LIST', 'Enter here a list of alias for your home page. Put only one alias per line, like :<br/>old-url.html<br/>or<br/>my-other-old-url.php?var=12&test=15<br>sh404SEF will do a 301 redirect to your home page if any one of those aliases is requested');
define('_COM_SEF_SH_INSERT_OUTBOUND_LINKS_IMAGE', 'Insert outbound links symbol');
define('_COM_SEF_TT_SH_INSERT_OUTBOUND_LINKS_IMAGE', 'If set to Yes, a visual symbol will be inserted next to every link targeting another website, to allow easier identification of these links.');
define('_COM_SEF_SH_OUTBOUND_LINKS_IMAGE_BLACK', 'Use black symbol');
define('_COM_SEF_SH_OUTBOUND_LINKS_IMAGE_WHITE', 'Use white symbol');
define('_COM_SEF_SH_OUTBOUND_LINKS_IMAGE', 'Outbound links color symbol');
define('_COM_SEF_TT_SH_OUTBOUND_LINKS_IMAGE', 'Both images have a transparent background. Select the black one if your site has a white background. Select the white one if your site has a dark background. These images are  /administrator/components/com_sef/images/external-white.png and external-black.png. They are 15x16 pixels in size.');

// V 1.3.3
define('_COM_SEF_DEFAULT_PARAMS_TITLE', 'Very adv.');
define('_COM_SEF_DEFAULT_PARAMS_WARNING', 'WARNING: change these values only if you know what you are doing! In case of wrongdoing, you could make damages you will have trouble repairing.');

// V 1.0.12
define('_COM_SEF_USE_CAT_ALIAS', 'Use category alias');
define('_COM_SEF_TT_USE_CAT_ALIAS', 'If set to <strong>Yes</strong>, sh404sef will use a category alias instead of its actual name every time that name is required to build a url');
define('_COM_SEF_USE_SEC_ALIAS', 'Use section alias');
define('_COM_SEF_TT_USE_SEC_ALIAS', 'If set to <strong>Yes</strong>, sh404sef will use a section alias instead of its actual name every time that name is required to build a url');
define('_COM_SEF_USE_MENU_ALIAS', 'Use menu alias');
define('_COM_SEF_TT_USE_MENU_ALIAS', 'If set to <strong>Yes</strong>, sh404sef will use a menu item alias instead of its actual title every time that title is required to build a url');
define('_COM_SEF_SH_ENABLE_TABLE_LESS', 'Use table-less output');
define('_COM_SEF_TT_SH_ENABLE_TABLE_LESS', 'If set to <strong>Yes</strong>, sh404sef will make Joomla use only div tags (no table tags) when outputing content, regardless of the template you are using. You should not have removed the Beez template for this to work. Beez template is installed by default with Joomla.<br /><strong>WARNING</strong> : you will have to adjust your template stylesheet to match this new html output format.');

// V 1.0.13
define( '_COM_SEF_JC_MODULE_CACHING_DISABLED', 'Caching for Joomfish language selection module has been disabled!');

//define('', '');
?>
