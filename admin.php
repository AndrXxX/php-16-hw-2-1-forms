<?php
$homeWorkNum = '2.2';
$homeWorkCaption = 'Обработка форм.';
$fileReady = false;
$fileName = 'tests.json';
$filePath = __DIR__ . '/uploadedFiles/'.$fileName;
$additionalHint = '';
if (isset($_FILES['myfile'])) {
    $file = $_FILES['myfile'];
}

/* проверяем отправлен ли файл и если да - проверяем соотвествие и перемещаем его в подпапку */
if (isset($file['name']) && !empty($file['name'])) {
    if ($file['type'] == 'application/json' &&
        $file['error'] == UPLOAD_ERR_OK &&
        move_uploaded_file($file['tmp_name'], $filePath)) {
        $fileReady = true;
    } else {
        $additionalHint = 'Файл не загружен (возможно тип файла не подходит), попробуйте еще раз.';
    }
}

/* проверяем чтобы файл уже был на сервере */
if (is_file($filePath)) {
    $fileReady = true;
}

/* Если нажали Очистить папку */
if (isset($_POST['ClearFilesFolder'])) {
    clear_dir(__DIR__ . '/uploadedFiles/');
    $additionalHint = "Папка с файлами очищена!";
    $fileReady = false;
}

// функция очищения папки от файлов
function clear_dir($dir)
{
    $list_temp = scandir($dir);
    unset($list_temp[0], $list_temp[1]);
    $list = array_values($list_temp);

    foreach ($list as $file) {
        unlink($dir . $file);
    }
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
    <p>Для этих целей можно использовать файл tests.json по ссылке: <a href="tests.json">открыть</a>, <a href="tests.json" download="">скачать</a>.</p>

    <form method="post" action="" enctype="multipart/form-data">
      <fieldset>
        <?php
        if (!$fileReady or isset($_POST['ShowAdminLoadForm'])) {
        ?>

        <!-- Форма загрузки файла, когда файл еще не загружен или нажали ShowAdminLoadForm -->
        <legend>Загрузка файла</legend>

        <label>Файл: <input type="file" name="myfile"></label>
        <hr>
        <p><?= ($fileReady) ? "Файл $fileName уже загружен, можно перейти к тестам" : $additionalHint ?></p>
        <div>
          <input type="submit" name="LoadFileToServer" value="Отправить новый файл на сервер">
          <input type="submit" name="ClearFilesFolder" value="Очистить папку"
                 title="При нажатии папка с загруженными файлами на сервере будет очищена">
          <?php if ($fileReady) { /* выводим кнопку перехода к тестам если файл есть на диске */?>
          <input type="submit" formaction="list.php" name="ShowTestsList" value="К тестам =>"
                 title="Перейти в выполнению тестов">
          <?php } ?>
        </div>

        <?php } else { ?>

        <!-- Форма загрузки файла, когда файл уже загружен -->
        <legend>Обработка файла</legend>
        <p>Файл <?= (isset($file) ? $file['name'] : 'tests.json') ?> загружен, можно перейти к выполнению тестов.</p>
        <hr>
        <p><?= $additionalHint ?></p>
        <div>
          <input type="submit" formaction="admin.php" name="ShowAdminLoadForm" value="<= Загрузить новый файл"
                 title="Вернуться к загрузке файла">
          <input type="submit" formaction="admin.php" name="ClearFilesFolder" value="Очистить папку"
                 title="При нажатии папка с загруженными файлами на сервере будет очищена">
          <input type="submit" formaction="list.php" name="ShowTestsList" value="К тестам =>"
                 title="Перейти в выполнению тестов">
        </div>

        <?php } ?>
      </fieldset>
    </form>
  </body>
</html>
