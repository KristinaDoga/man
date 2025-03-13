
<?
/*
 ----------------------------------------------------------------------------------
 dScaner Class - START
 ----------------------------------------------------------------------------------
*/

/*
*
*   Класс - dScaner для сканирования директорий на наличие вредоносного кода в 
*   указанных типах файлов
*   
*   Разработчик: Денис Ушаков
*   Дата разработки: 03-04-2012
*   Версия разработки: 0.0.3
*
*/

Class dScaner {

    // преобразуем входной параметр в массив 
    // $get_str - список параметров
    // $separator - разделитель параметров в списке
    function request($get_str, $separator)
    {
        if (isset($get_str) && !empty($get_str))
        {   
            // эксплоадим строку в массив и возвращаем его
            $obj = explode($separator, $get_str);
            return $obj;
        }
        else
        {
            return false;
        }
    }

    /*
    *
    *   Функция поиска в файлах вхождения заданной строки:
    *
    *   $this->find($path, $files_allowed, $requested_string);
    *   
    *   $path - путь до директории, от которой отталкиваться при сканировании
    *   $files_allowed - список файлов, которые подвергаются сканированию
    *   $requested_string - строка поиска
    *
    */
    function find($path = './', $files_allowed, $requested_string)
    {
        // исключаемые ссылки на директории и файлы, которые будут игнорироваться
        $dir_disallow = array('.', '..', '.htaccess', '.git');

        if(is_dir($path))
        {
           $temp = opendir($path);
           while (false !== ($dir = readdir($temp))) 
           {
                if ((is_dir($path . $dir)) && 
                    (!in_array($dir, $dir_disallow)) ) 
                {
                    // если директория - сканируем её
                    $sub_dir = $path . $dir . '/';
                    $this->find($sub_dir, $files_allowed, $requested_string);
                } 
                elseif ((is_file($path . $dir)) && 
                        (!in_array($dir, $dir_disallow)) && 
                        (strpos($dir, $files_allowed) == true) &&
                        (strpos($dir, '_BACKUP') == false) )
                {
                    // Если файл
                    // получаем полный путь до него
                    $in_dir_file = $path . $dir;
                    // считываем файл в строку
                    $temporary_file = file_get_contents($in_dir_file);  
                    // флаг найденного вхождения искомой строки
                    $file_founded = false;

                    // разбиваем файл на строки
                    $tf_strings = explode("\n", $temporary_file);
                    // обрабатываем каждую отдельно
                    foreach ($tf_strings AS $item)
                    {
                        $item = strval($item);
                        // если в строке есть вхождения искомого запроса
                        if (strpos($item, $requested_string) !== false)
                        { 
                            $file_founded = true;
                        }
                    }
                    // если в файле найдена строка
                    if ($file_founded)
                    {
                        // выводим путь до файла в котором найдено вхождение
                        print "<span style='display:block; 
                                            padding:5px; 
                                            border:1px solid #1f4f18;
                                            background-color:#d5f5ce; 
                                            font-size:12px;
                                            line-height:16px;
                                            font-family:tahoma, sans-serif;
                                            margin-bottom:-15px;'>" . $in_dir_file . " - в файле обнаружена искомая строка.<br>
                                </span><br>";                        
                    }
                }
           }
           closedir($temp);
        } 
    }

    /*
    *
    *   Функция сканирования вредоносного кода:
    *
    *   $this->scan($path, $files_allowed, $requested_string);
    *   
    *   $path - путь до директории, от которой отталкиваться при сканировании
    *   $files_allowed - список файлов, которые подвергаются сканированию
    *   $requested_string - строка, по которой определяется наличие вредоносного кода
    *
    */
    function scan($path = './', $files_allowed, $requested_string)
    {
        // исключаемые ссылки на директории и файлы
        $dir_disallow = array('.', '..', '.htaccess', '.git');

        if(is_dir($path))
        {
           $temp = opendir($path);
           while (false !== ($dir = readdir($temp))) 
           {
                if ((is_dir($path . $dir)) && 
                    (!in_array($dir, $dir_disallow)) ) 
                {
                    // если директория - сканируем её
                    $sub_dir = $path . $dir . '/';
                    $new_parent_dir = $path . $dir;
                    $this->scan($sub_dir, $files_allowed, $requested_string, $new_parent_dir);
                } 
                elseif ((is_file($path . $dir)) && 
                        (!in_array($dir, $dir_disallow)) && 
                        (strpos($dir, $files_allowed) == true) &&
                        (strpos($dir, '_BACKUP') == false) )
                {
                    // Если файл
                    // получаем полный путь до него
                    $in_dir_file = $path . $dir;
                    // считываем файл в строку
                    $temporary_file = file_get_contents($in_dir_file);  
                    // флаг бекапа файла                                   
                    $create_backup = false;                    

                    // разбиваем файл на строки и считываем каждую отдельно
                    $tf_strings = explode("\n", $temporary_file);
                    // индекс строки файла
                    $str_index = 0;
                    // каждую строку обрабатываем отдельно
                    foreach ($tf_strings AS $item)
                    {
                        $item = strval($item);
                        if (strpos($item, $requested_string) !== false)
                        { 
                            // если в строке есть вхождения искомого запроса
                            // флаг бекапа файла, в котором найден вредоносный код
                            $create_backup = true; 
                            // удаляем всю строку с вредоносным кодом
                            unset($tf_strings[$str_index]);
                        }
                        $str_index++;
                    }

                    // создаём бэкап
                    if ($create_backup)
                    {
                        // меняем права в папке в которой находимся чтобы иметь возможность писать в неё
                        chmod($path, 0777);
                        // формируем имя БЭКАПа файла
                        $temp_file_backup = $in_dir_file.'_BACKUP';
                        // сохраняем БЭКАП файла рядом с исходным
                        file_put_contents($temp_file_backup, $temporary_file);
                        // собираем очищенный файл в строку
                        $scanned_file = implode("\n", $tf_strings);
                        // сохраняем очищенный файл
                        if (file_put_contents($in_dir_file, $scanned_file))
                        {   
                            // перезаписали удачно
                            print "<span style='display:block; 
                                                padding:5px; 
                                                border:1px solid #1f4f18;
                                                background-color:#d5f5ce; 
                                                font-size:12px;
                                                line-height:16px;
                                                font-family:tahoma, sans-serif;
                                                margin-bottom:-15px;'>" . $in_dir_file . " - Файл очищен. (+ BACKUP) <br>
                                    </span><br>";
                        }
                        else
                        {
                            // перезапись не удалась
                            print "<span style='display:block; 
                                                padding:5px; 
                                                border:1px solid #822121;
                                                background-color:#ea7575; 
                                                font-size:12px;
                                                line-height:16px;
                                                font-family:tahoma, sans-serif;
                                                margin-bottom:-15px;'>".$in_dir_file ." - Файл НЕ очищен.
                                    </span><br>";  
                        }
                        // меняем права в папке в которой находимся обратно на 755
                        chmod($path, 0755);                       
                    }
                }
           }
           closedir($temp);
        } 
    }

    /*
    *
    *   Функция восстановления БЭКАПОВ файлов
    *
    *   $this->restore_backups($path, $files_allowed);
    *   
    *   $path - путь до директории, от которой отталкиваться при восстановлении
    *   $files_allowed - список файлов, которые подвергаются восстановлению
    *
    */
    function restore_backups($path = './', $files_allowed)
    {
        // исключаемые ссылки на директории и файлы
        $dir_disallow = array('.', '..', '.htaccess', '.git');
        if(is_dir($path))
        {
           $temp = opendir($path);
           while (false !== ($dir = readdir($temp))) 
           {
                if ((is_dir($path . $dir)) && 
                    (!in_array($dir, $dir_disallow)) ) 
                {
                    // если директория - сканируем её
                    $sub_dir = $path . $dir . '/';
                    $this->restore_backups($sub_dir, $files_allowed);
                } 
                elseif ((is_file($path . $dir)) && 
                        (!in_array($dir, $dir_disallow)) && 
                        (strpos($dir, $files_allowed) == true) )
                {
                    // Если файл
                    // получаем полный путь до него
                    $in_dir_file = $path . $dir;
                    if (is_file($in_dir_file.'_BACKUP'))
                    {
                        // БЭКАП существует, получаем его содержимое
                        $temporary_file_from_backup = file_get_contents($in_dir_file.'_BACKUP');
                        // восстанавливаем бэкап файла
                        if (file_put_contents($in_dir_file, $temporary_file_from_backup))
                        {   
                            // удаляем бэкап
                            unlink($_SERVER['DOCUMENT_ROOT'].'/'.$in_dir_file.'_BACKUP');
                            // бэкап восстановили
                            print "<span style='display:block; 
                                                padding:5px; 
                                                border:1px solid #1f4f18;
                                                background-color:#d5f5ce; 
                                                font-size:12px;
                                                line-height:16px;
                                                font-family:tahoma, sans-serif;
                                                margin-bottom:-15px;'>".$in_dir_file ." - восстановлен.
                                    </span><br>";                  
                        }
                        else
                        {
                            // бэкап НЕ восстановили
                            print "<span style='display:block; 
                                                padding:5px; 
                                                border:1px solid #822121;
                                                background-color:#ea7575; 
                                                font-size:12px;
                                                line-height:16px;
                                                font-family:tahoma, sans-serif;
                                                margin-bottom:-15px;'>".$in_dir_file ." - НЕ восстановлен.
                                    </span><br>";  
                        }
                    }
                }
           }
           closedir($temp);
        } 
    }        
}

/*
 ----------------------------------------------------------------------------------
 dScaner Class - END
 ----------------------------------------------------------------------------------
*/

?>