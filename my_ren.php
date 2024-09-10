<?php

// Определяем доступные опции
$options = getopt("", ["folder:", "suffix:", "mask::"]);

// Проверяем, что папка и суффикс заданы
if (!isset($options['folder']) || !isset($options['suffix'])) {
    die("Использование: php script.php --folder=путь_к_папке --suffix=суффикс [--mask=маска_файлов]\n");
}

$folder = rtrim($options['folder'], '/');
$suffix = $options['suffix'];
$mask = isset($options['mask']) ? $options['mask'] : '*'; // Опциональная маска файлов

// Проверяем, существует ли папка
if (!is_dir($folder)) {
    die("Ошибка: Папка не существует\n");
}

// Получаем список файлов по маске
$files = glob($folder . '/' . $mask);

// Проверяем, что файлы найдены
if (empty($files)) {
    die("Ошибка: В папке нет файлов, соответствующих маске\n");
}

//Логирование
$file_name_log = date("Ymd_His") . ".log";
$curr_dir_log = __DIR__ . '/logs';
if (!file_exists($curr_dir_log)){
    mkdir($curr_dir_log);
}




// Переименовываем файлы
foreach ($files as $file) {
    if (is_file($file)) {
        $pathinfo = pathinfo($file);

        //Работа с suffix
        $pathinfo['filename'] = $suffix ? preg_replace("/{$suffix}/i", '', $pathinfo['filename']) : $pathinfo['filename'];

        //При совпадении маски меняем 10 символ на _
        //2022-06-17-22-42-25-814_com.pocapp.castlecats.jpg
        //2022-06-17_22-42-25-814_com.pocapp.castlecats.jpg
        if(preg_match("/^(\d{4}-\d{2}-\d{2})-(\d{2}-\d{2}-\d{2})/i", $pathinfo['filename'])){
            $pathinfo['filename'] = substr_replace($pathinfo['filename'], '_', 10, 1);
        }

        //Пересобираем файл
        $newName = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.' . $pathinfo['extension'];

        if (rename($file, $newName)) {
            echo "Файл {$file} переименован в {$newName}\n";
            file_put_contents( "{$curr_dir_log}/{$file_name_log}", "Файл {$file} переименован в {$newName}\n", FILE_APPEND);
        } else {
            echo "Не удалось переименовать файл {$file}\n";
            file_put_contents( "{$curr_dir_log}/{$file_name_log}", "Не удалось переименовать файл {$file}\n", FILE_APPEND);
        }
    }
}

?>