<?php
$homeWorkNum = '2.2';
$homeWorkCaption = 'Обработка форм.';
$testReady = false;
$filesPath = __DIR__ . '/uploadedFiles/';
$additionalHint = '';
$labelStyle = '';
$warningStyle = 'font-weight: 700; color: red;';
$rightStyle = 'color: green;';
$errorCounts = 0;

/* проверяем передался ли номер теста */
if (isset($_GET['testNum'])) {
    $testNum = $_GET['testNum'];
} elseif (isset($_POST['testNum'])) {
    $testNum = $_POST['testNum'];
}
/* извлекаем тест */
if (isset($testNum)) {
    $test = getSelectedTest($testNum, $filesPath);
    $testReady = ($test !== false) ? $test : false;
}

/* функция возвращает массив с именами json-файлов (с тестами) */
function getNamesJson($dir)
{
    $array = array_diff(scandir($dir), array('..', '.'));
    sort($array);
    return $array;
}

/* Функция получает на входе номер теста и папку с файлами, а возращает сам тест или false */
function getSelectedTest($testNum, $filesPath)
{
    if (isset($testNum) && isset($filesPath)) {
        $testFilesList = getNamesJson($filesPath); /* список названий файлов с тестами */
        if (count($testFilesList) > 0 && count($testFilesList) > $testNum && isset($testFilesList[$testNum])) {
            return json_decode(file_get_contents($filesPath . $testFilesList[$testNum]), true);
        }
    }
    return false;
}

/* Функция получает на входе код варианта ответа, ответ и правильные ответы и возвращает true если была допущена
ошибка или false - если нет */
function isError($labelName, $answer, $rightAnswers)
{
    if ((isset($_POST[$labelName]) && $_POST[$labelName] === $answer && !in_array($_POST[$labelName], $rightAnswers)) or
        (in_array($answer, $rightAnswers) && isset($_POST[$labelName]) === false)) {
        return true;
    }
    return false;
}

/* Функция получает на входе код варианта ответа, ответ и правильные ответы, стили для правильного и неправильного
ответов и возвращает подходящий стиль */
function elementStyle($labelName, $answer, $rightAnswers, $warningStyle, $rightStyle)
{
    if (isset($_POST[$labelName]) && $_POST[$labelName] === $answer) {
        if (in_array($_POST[$labelName], $rightAnswers)) {
            return $rightStyle;
        } else {
            return $warningStyle;
        }
    } elseif (in_array($answer, $rightAnswers)) {
        return $warningStyle;
    }
    return '';
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
        <legend><?= ($testReady && isset($test) ? $test['testName'] : 'Тесты') ?></legend>

        <?php if ($testReady && isset($test)) {
            $needChecked = '';
            foreach ($test['questions'] as $questionNum => $question):
                $questionType = ($question['type'] === 'single' ? 'radio' : 'checkbox');
                $i = 0;
        ?>

        <fieldset>
          <legend><?= $question['question'] ?></legend>

          <?php
              foreach ($question['answers'] as $answerNum => $answer):
                  ++$i;
                  $color = 'black';
                  $fontWeight = 'normal';
                  $labelName = ($question['type'] === 'single' ? $questionNum : $questionNum . '|' . $answerNum);
                  /*Если label - это чекбокс, то делаем имя в таком формате: "вопрос + | + № ответа", иначе - только имя вопроса.
                  Это нужно для правильной работы переключателей и передачи параметров для проверки теста */

                  $needChecked = ((!isset($_POST['ShowTestResults']) && $i === 1 && $questionType === 'radio') ||
                  (isset($_POST['ShowTestResults']) && isset($_POST[$labelName]) && $_POST[$labelName] === $answer) ? 'Checked' : '');
                  /* Расставляем галки/радио-кнопки правильно: если кнопка ShowTestResults не была нажата, то для первых
                  элементов типа radio, ставим атрибут Checked, если кнопка была нажата - загружаем как было установлено
                  пользователем */

                  if (isset($_POST['ShowTestResults'])) {

                      $labelStyle = elementStyle($labelName, $answer, $question['rightAnswers'], $warningStyle, $rightStyle);
                      /* определяем стиль элемента (в зависимости от наличия / отсутствия ошибки) */

                      $errorCounts = (isError($labelName, $answer, $question['rightAnswers']) ? ++$errorCounts : $errorCounts);
                      /* если допущена ошибка - увеличиваем счетчик ошибок */
                  }
          ?>

          <label style="<?= $labelStyle ?>"><input type="<?= $questionType ?>" name="<?= $labelName ?>"
                 value="<?= $answer ?>" <?= $needChecked ?>><?= $answer ?>
          </label>

          <?php endforeach; ?>

        </fieldset>

        <?php
            endforeach;
            /* вывод подсказки при нажатии ShowTestResults */
            if (isset($_POST['ShowTestResults'])) {
                if ($errorCounts === 0) {
                    $additionalHint = 'Вы правильно ответили на все вопросы! Поздравляем!';
                } else {
                    $additionalHint = 'Количество ошибок, допущенных при выполнении теста: ' . $errorCounts . ' шт.';
                }
            }
            ?>

        <hr>

        <?php } ?>

        <p><?= ($testReady) ? $additionalHint : 'Не удалось извлечь список тестов, попробуйте вернуться и загрузить файл заново.' ?></p>
        <div>
          <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<<= Вернуться к загрузке файла"
                 title="Вернуться к загрузке файла">
          <input type="submit" formaction="list.php" name="ShowListForm" value="<= Вернуться к выбору теста"
                 title="Вернуться к выбору теста">

          <?php if ($testReady) { ?>
            <input type="hidden" name="testNum" value="<?= (isset($testNum) ? $testNum : 0) ?>">
            <input type="submit" formaction="test.php" name="ShowTestResults" value="Проверить"
                   title="Проверить результаты теста">
          <?php } ?>
        </div>

      </fieldset>
    </form>
  </body>
</html>
