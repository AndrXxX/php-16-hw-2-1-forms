<?php
$homeWorkNum = '2.2';
$homeWorkCaption = 'Обработка форм.';
$fileReady = false;
$filesPath = __DIR__ . '/uploadedFiles/';
$additionalHint = '';
$additionalHintStyle = '';
$warningStyle = 'font-weight: 700; color: red;';

/* проверяем нажимали ли кнопку LoadFileToServer */
if (isset($_POST['LoadFileToServer'])) {

    $fileReady = (isset($_FILES['myFile'])) ? checkFile($_FILES['myFile'], $filesPath) : false;
    /* Проверяем файл с помощью функции, в зависимости от результата получаем подсказку */
    switch ($fileReady) {
        case 'FileNotSet':
            $additionalHint = 'Файл не загружен, так как не был выбран.';
            break;
        case 'WrongFileType':
            $additionalHint = 'Файл не загружен (тип файла не подходит).';
            break;
        case 'ErrorLoading':
            $additionalHint = 'Произошла ошибка при загрузке файла, попробуйте повторить.';
            break;
        case 'SameFileExist':
            $additionalHint = 'Файл не загружен, так как на сервере уже есть идентичный файл.';
            break;
        case 'FileStructureNotValid':
            $additionalHint = 'Структура загружаемого файла не подходит, попробуйте загрузить другой файл.';
            break;
        case 'FileNotMoved':
            $additionalHint = 'Произошла ошибка при обработке файла на сервере, попробуйте повторить.';
            break;
        case false:
            $additionalHint = 'Ошибка загрузки, попробуйте повторить.';
            break;
        case true:
            break;
    }
    if ($fileReady !== true and $fileReady !== false) {
        $fileReady = false;
        $additionalHintStyle = $warningStyle; /* выделяем стиль подсказки */
    }
}

/* проверяем есть ли на сервере загруженные файлы */
if (count(getNamesJson($filesPath)) > 0) {
    $fileReady = true;
}

/* Если нажали Очистить папку */
if (isset($_POST['ClearFilesFolder'])) {
    clearDir(__DIR__ . '/uploadedFiles/');
    $additionalHint = "Папка с файлами очищена!";
    $fileReady = false;
}

function checkFile($file, $filesPath)
{
    /* Функция возвращает true, если все в порядке или ошибку если что-то не так */

    if (!isset($file['name']) or empty($file['name'])) {
        return 'FileNotSet';
    }

    if (isset($file['type'])) {
        if ($file['type'] !== 'application/json') {
            return 'WrongFileType';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'ErrorLoading';
        }
    }

    if (isset($file['tmp_name'])) {
        if (in_array(hash_file('md5', $file['tmp_name']), get_hash_json($filesPath))) {
            return 'SameFileExist';
        }

        if (!isset($file['tmp_name']['testName']) or !isset($file['tmp_name']['questions'])) {
            return 'FileStructureNotValid';
        }

        if (!move_uploaded_file($file['tmp_name'], $filesPath . setNameJson($filesPath))) {
            return 'FileNotMoved';
        }
    }

    return true;
}

/* функция очищения папки от файлов */
function clearDir($dir)
{
    $list = array_values(getNamesJson($dir));
    foreach ($list as $file) {
        unlink($dir . $file);
    }
}

/* функция сканирует папку и возвращает первое незанятое название файла 1.json, 2.json и т.д. */
function setNameJson($dir)
{
    $filesList = getNamesJson($dir);
    $fileName = (count($filesList) + 1) . '.json';
    $i = 2;
    while (is_file($dir . $fileName)) {
        $fileName = (count($filesList) + $i) . '.json';
        $i++;
    }
    return $fileName;
}

/* функция возвращает массив с именами json-файлов (с тестами) */
function getNamesJson($dir)
{
    $array = array_diff(scandir($dir), array('..', '.'));
    sort($array);
    return $array;
}

/* функция возвращает массив с хешами json-файлов */
function get_hash_json($dir)
{
    $hash_list = array();
    if (count(getNamesJson($dir)) > 0) {
        foreach (getNamesJson($dir) as $file) {
            $hash_list[] = hash_file('md5', $dir . $file);
        }
    }
    return $hash_list;
}

?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <title>Домашнее задание по теме <?= $homeWorkNum ?> <?= $homeWorkCaption ?></title>
    <meta charset="utf-8">
    <style>
      form {
        display: inline-block;
      }

      div {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <h1>Интерфейс загрузки файла</h1>
    <p>На этой странице необходимо выбрать и загрузить json-файл с тестами для дальнейшей работы.</p>
    <p>Для этих целей можно использовать файлы: <a href="./examplesTests/english.json" download="">english.json</a>,
      <a href="./examplesTests/multiplication.json" download="">multiplication.json</a> и
      <a href="./examplesTests/units.json" download="">units.json</a> .</p>
    <p>В форму загрузки встроена проверка загружаемого файла на наличие на сервере (по хешу).</p>
    <p>Если загружаемый файл уже есть на сервере, то он не будет загружен.</p>

    <form method="post" action="" enctype="multipart/form-data">

      <fieldset>
        <legend>Загрузка файлов</legend>
        <label>Файл: <input type="file" name="myFile"></label>
        <hr>
        <p style="<?= $additionalHintStyle ?>"><?= $additionalHint ?></p>
        <div>
          <input type="submit" name="LoadFileToServer" value="Отправить новый файл на сервер">
        </div>
      </fieldset>

      <?php if ($fileReady) { ?>
      <fieldset>
        <legend>Список файлов</legend>
        <p>Json-файлы с тестами, загруженные на сервер:</p>

        <ul>
            <?php foreach (getNamesJson($filesPath) as $test) : /* Выводим список файлов и названий тестов */ ?>
              <li><?= $test . ' / ' . json_decode(file_get_contents($filesPath . $test), true)['testName'] ?></li>
            <?php endforeach; ?>
        </ul>

        <p>Можно перейти к выбору теста.</p>
        <hr>
        <div>
          <input type="submit" name="ClearFilesFolder" value="Очистить папку"
                 title="При нажатии папка с загруженными файлами на сервере будет очищена">
          <input type="submit" formaction="list.php" name="ShowTestsList" value="К тестам =>"
                 title="Перейти в выполнению тестов">
        </div>
      </fieldset>
      <?php } ?>
    </form>
  </body>
</html>
