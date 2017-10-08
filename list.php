<?php
$homeWorkNum = '2.2';
$homeWorkCaption = 'Обработка форм.';
$testsReady = false;
$additionalHint = '';

/* проверяем есть ли файл и если да - получаем его содержимое */
if (is_file(__DIR__ . '/uploadedFiles/tests.json')) {
    $tests = json_decode(file_get_contents(__DIR__ . '/uploadedFiles/tests.json'), true);
    $testsReady = true;
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
    <h1>Интерфейс выбора варианта теста</h1>

    <form method="post" enctype="multipart/form-data">
      <fieldset>
        <?php if ($testsReady && isset($tests)) { ?>

        <legend>Выберите один из <?= count($tests) ?> вариантов теста, который вы желаете пройти:</legend>

        <?php
            $i = 0;
            foreach ($tests as $testNum => $test):
                $i++;
                $needChecked = ($i == 1 ? 'Checked' : '');
        ?>

        <p><label><input type="radio" name="testNum"
                         value="<?= $testNum ?>" <?= $needChecked ?>><?= $test['testName'] ?></label></p>

        <?php endforeach; ?>

        <hr>
        <p><?= $additionalHint ?></p>
        <div>
          <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<= Вернуться"
                 title="Вернуться к загрузке файла">
          <input type="submit" formaction="test.php" formmethod="get" name="ShowTest" value="Пройти тест =>"
                 title="Перейти в выполнению выбранного теста">
        </div>

        <?php } else { ?>

        <legend>Тесты</legend>
        <p>Не удалось извлечь список тестов, попробуйте вернуться и загрузить файл заново.</p>
        <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<= Вернуться"
               title="Вернуться к загрузке файла">

        <?php } ?>

      </fieldset>
    </form>
  </body>
</html>