<?php

namespace Model\Algorithm;
use Model\Exception as Exception;

class Error {

    protected $message;

    public function __construct(\Exception $e) {
        if (is_a($e, '\Model\Exception\Expression'))
            $this->Expression($e);
        elseif (is_a($e, '\Model\Exception\ExpressionAnswer'))
            $this->ExpressionAnswer($e);
        elseif (is_a($e, '\Model\Exception\Question'))
            $this->Question($e);
        elseif (is_a($e, '\Model\Exception\Action'))
            $this->Action($e);
        elseif (is_a($e, '\Model\Exception\Executor'))
            $this->Executor($e);
        else
            $this->Exception($e);
    }

    public function getMessage() {
        return $this->message;
    }

    public function getHtmlMessage() {
        $message = str_replace("\n", "<br>", $this->message);
        $message = str_replace("\s", "&nbsp;", $message);
        return $message;
    }


    protected function Question(Exception\Question  $e) {
        switch ($e->getCode()) {
            case Exception\Question::DEFAULT_ANSWER_IS_NOT_SET:
                $this->message =
                    "Не задан ответ по умолчанию.\n".
                    "(скорее всего вопрос отключен в контексте).\n".
                    $e->getMessage();
                break;
            case Exception\Question::DOCUMENT_IS_NOT_INITIALIZED:
                $this->message =
                    "Не найдено свойство документа, привязанное к вопросу\n".
                    "(скорее всего оно отключено для контекста)\n".
                    $e->getMessage();
                break;
            case Exception\Question::DOCUMENT_GENERAL_IS_NOT_INITIALIZED:
                $this->message =
                    "Не найден документ, привязанный к вопросу\n".
                    "(скорее всего он отключен для контекста)\n".
                    $e->getMessage();
                break;
            case Exception\Question::ANSWER_NOT_FOUND:
                $this->message =
                    "Не найден ответ для вопроса.\n".
                    "(скорее всего ошибка в передаче ответа в экспертную систему).\n".
                    $e->getMessage();
                break;
            default:
                $this->message =
                    "Ошибка в вопросе: ".$e->getMessage();
                break;
        }
    }



    protected function Expression(Exception\Expression  $e) {
        switch ($e->getCode()) {
            case Exception\Expression::ARGUMENT_IS_NOT_SET:
                $this->message =
                    "При вычислении выражения не найден один из аргументов.\n".
                    $e->getMessage();
            break;
            default:
                $this->message =
                    "Ошибка в выражении: ".$e->getMessage();
            break;
        }
    }


    protected function ExpressionAnswer(Exception\ExpressionAnswer  $e) {
        switch ($e->getCode()) {
            case Exception\ExpressionAnswer::CONDITION_EXPRESSION_IS_INVALID:
                $this->message =
                    "При вычислении условия из ответа выражения произошла ошибка.\n".
                    "* ID Выражения: {$e->expressionId}\n".
                    "* ID Ответа: {$e->answerId}\n".
                    "* Формула условия: {$e->formula}\n".
                    "* Аргументы: ".str_replace("\n", '', var_export($e->params, true))."\n";
                break;
            case Exception\ExpressionAnswer::CONDITION_ARGUMENT_NOT_DEFINED:
                $this->message =
                    "В условии используется аргумент, не привязанный к выражению.\n".
                    "* ID Выражения: {$e->expressionId}\n".
                    "* ID Ответа: {$e->answerId}\n".
                    "* Формула условия: {$e->formula}\n".
                    "* Недостающий аргумент: \${$e->argumentNum}\n";
                break;
            case Exception\ExpressionAnswer::FORMULA_EXPRESSION_IS_INVALID:
                $this->message =
                    "При вычислении формулы из ответа выражения произошла ошибка.\n".
                    "* ID Выражения: {$e->expressionId}\n".
                    "* ID Ответа: {$e->answerId}\n".
                    "* Формула: {$e->formula}\n".
                    "* Аргументы: ".str_replace("\n", '', var_export($e->params, true))."\n";
                break;
            case Exception\ExpressionAnswer::FORMULA_ARGUMENT_NOT_DEFINED:
                $this->message =
                    "В формуле используется аргумент, не привязанный к выражению.\n".
                    "* ID Выражения: {$e->expressionId}\n".
                    "* ID Ответа: {$e->answerId}\n".
                    "* Формула: {$e->formula}\n".
                    "* Недостающий аргумент: \${$e->argumentNum}\n";
                break;

            default:
                $this->message =
                    "Ошибка в выражении: ".$e->getMessage();
                break;
        }
    }


    protected function Executor(Exception\Executor  $e) {
        switch ($e->getCode()) {
            case Exception\Executor::UNKNOWN_NODE_CLASS:
                $this->message =
                    "При выполнении алгоритма обнаружен Node неизвестного класса.\n".
                    $e->getMessage();
                break;
            case Exception\Executor::ALGORITHM_LOOP_FOUND:
                $this->message =
                    "При выполнении алгоритма возникло зацикливание.\n".
                    $e->getMessage();
                break;
            default:
                $this->message =
                    "Ошибка при выполнении алгоритма: ".$e->getMessage();
                break;
        }
    }


    protected function Action(Exception\Action  $e) {
        switch ($e->getCode()) {
            case Exception\Action::RUN_FAILED:
                $this->message =
                    "Действие не было выполнено.\n".
                    $e->getMessage();
                break;
            default:
                $this->message =
                    "Ошибка в действии: ".$e->getMessage();
                break;
        }
    }


    protected function Exception(\Exception $e) {
        $this->message = $e->getMessage();
    }
}