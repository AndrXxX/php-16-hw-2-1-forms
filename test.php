<?php
$homeWorkNum = '2.2';
$homeWorkCaption = 'Обработка форм.';
$testReady = false;
$additionalHint = '';
$errorCounts = 0;


/* делаем проверки и извлекаем тест из файла */
if (is_file(__DIR__ . '/uploadedFiles/tests.json') && (isset($_GET['testNum']) or isset($_POST['testNum']))) {
    $tests = json_decode(file_get_contents(__DIR__ . '/uploadedFiles/tests.json'), true);
    if (isset($_GET['testNum'])) {
        $testNum = $_GET['testNum'];
    } elseif (isset($_POST['testNum'])) {
        $testNum = $_POST['testNum'];
    }
    $test = (isset($testNum) ? $tests[$testNum] : 0);
    $testReady = true;
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
  <h1>Интерфейс прохождения выбранного теста</h1>

  <form method="post" enctype="multipart/form-data">
    <fieldset>
      <?php if ($testReady && isset($test)) { ?>

      <legend><?= $test['testName'] ?></legend>

      <?php
          foreach ($test['main'] as $questionNum => $question):
              $questionType = ($question['type'] == 'single' ? 'radio' : 'checkbox');
              $i = 0;
      ?>

      <fieldset>
        <legend><?= $question['question'] ?></legend>

        <?php
                foreach ($question['answers'] as $answerNum => $answer):
                    ++$i;
                    $color = 'black';
                    $fontWeight = 'normal';
                    $labelName = ($question['type'] == 'single' ? $questionNum : $questionNum . '|' . $answerNum);
                    /*Если label - это чекбокс, то делаем имя в таком формате: "вопрос + | + № ответа", иначе - только имя вопроса.
                    Это нужно для правильной работы переключателей и передачи параметров для проверки теста */

                    if (isset($_POST['ShowTestResults'])) {
                        /* Если нажали ShowTestResults (проверка результатов) - расставляем правильно галки и проверяем результат
                        (для правильного выбора делаем цвет текста зеленым, для неправильного (и для невыбранных правильных значений)
                        - красным и выделяем жирным) */
                        $needChecked = '';
                        if (isset($_POST[$labelName]) && $_POST[$labelName] === $answer) {
                            $needChecked = 'Checked';
                            if (in_array($_POST[$labelName], $question['rightAnswers'])) {
                                $color = 'Green';
                            } else {
                                $color = 'Red';
                                $fontWeight = 'Bold';
                                $errorCounts++;
                            }
                        } elseif (in_array($answer, $question['rightAnswers'])) {
                            $color = 'Red';
                            $fontWeight = 'Bold';
                            if (isset($_POST[$labelName]) === false) {
                              $errorCounts++;
                            }
                        }

                    } else {
                        /* Если кнопка ShowTestResults не была нажата, то для первых элементов типа radio,
                        то ставим атрибут Checked */
                        $needChecked = ($i === 1 && $questionType !== 'checkbox' ? 'Checked' : '');
                    }
        ?>

        <label style="color: <?= $color ?>; font-weight: <?= $fontWeight ?>"><input type="<?= $questionType ?>" name="<?= $labelName ?>"
                                                   value="<?= $answer ?>" <?= $needChecked ?>><?= $answer ?>
        </label>

        <?php
                endforeach;
                /* вывод подсказки при нажатии ShowTestResults */
                if (isset($_POST['ShowTestResults'])) {
                    if ($errorCounts == 0) {
                        $additionalHint = 'Вы правильно ответили на все вопросы! Поздравляем!';
                    } else {
                        $additionalHint = 'Количество ошибок, допущенных при выполнении теста: ' . $errorCounts . ' шт.';
                    }
                }
        ?>

      </fieldset>

      <?php endforeach; ?>
      <hr>
      <p><?= $additionalHint ?></p>
      <div>
        <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<<= Вернуться к загрузке файла"
               title="Вернуться к загрузке файла">
        <input type="submit" formaction="list.php" name="ShowAdminForm" value="<= Вернуться к выбору теста"
               title="Вернуться к выбору теста">
        <input type="hidden" name="testNum" value="<?= (isset($testNum) ? $testNum : 0) ?>">
        <input type="submit" formaction="test.php" name="ShowTestResults" value="Проверить"
               title="Проверить результаты теста">
      </div>

      <?php } else { ?>

      <legend>Тесты</legend>
      <p>Не удалось извлечь список тестов, попробуйте вернуться и загрузить файл заново.</p>
      <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<<= Вернуться"
             title="Вернуться к загрузке файла">

      <?php } ?>

    </fieldset>
  </form>
  </body>
</html>
