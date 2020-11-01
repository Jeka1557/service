<?php


namespace PT;
use TP;

define('OF_OOO', 1);
define('OF_AO', 2);
define('OF_ZAO', 3);
define('OF_AKKOR', 4);
define('OF_OAO', 5);
define('OF_DNP', 6);
define('OF_PAO', 7);
define('OF_GUP', 8);
define('OF_UNKNOWN', 0);

define('ANS_YES', 1);
define('ANS_NO', 2);

//Управляющая компания
define('MC_YES', 1);
define('MC_NO', 2);


class AnswerMapId extends TP\UInt2 {}

class ActionId extends TP\UInt2 {}

class WorkspaceId extends TP\UInt2 {}

class AlgorithmId extends TP\UInt2 {}

class QuestionId extends TP\UInt2 {}

class ComplexQuestionId extends TP\UInt2 {}

class AnswerId extends TP\UInt2 {}

class PageId extends TP\UInt4 {}

class RiskId extends TP\UInt2 {}

class RiskGeneralId extends TP\UInt2 {}

class InfoId extends TP\UInt2 {}

class DocumentId extends TP\UInt2 {}

class DocumentGeneralId extends TP\UInt2 {}

class ConclusionId extends TP\UInt2 {}

class ExpressionId extends TP\UInt2 {}

class MessageId extends TP\UInt2 {}


class ContextId extends TP\TInt {

    protected static $min = -1;
    protected static $max = 65535;

    const DEFAULT_ID = 0;
    const EMPTY_ID = -1;
}

class LinkedHTML extends TP\Text\Html {}

class GroupId extends TP\UInt2 {}

class User extends TP\Str\WordEn {}


class ActionIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\ActionId';
}

class WorkspaceIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\WorkspaceId';
}

class AlgorithmIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\AlgorithmId';
}

class QuestionIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\QuestionId';
}

class ComplexQuestionIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\ComplexQuestionId';
}

class AnswerIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\AnswerId';
}

class RiskGeneralIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\RiskGeneralId';
}

class RiskIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\RiskId';
}

class InfoIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\InfoId';
}

class ExpressionIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\ExpressionId';
}

class DocumentIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\DocumentId';
}

class DocumentGeneralIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\DocumentGeneralId';
}

class GroupIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\GroupId';
}

class ContextIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\ContextId';
}

class Users extends TP\Arr\Arr {
    protected static $typeClass = '\PT\User';
}

class ConclusionIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\ConclusionId';
}

class MessageIds extends TP\Arr\Arr {
    protected static $typeClass = '\PT\MessageId';
}


class EntityType extends TP\Set {

    const ALGORITHM = 1;
    const QUESTION = 2;
    const RISK = 3;
    const DOCUMENT = 4;
    const INFO = 5;
    const DOCUMENT_GENERAL = 6;
    const RISK_GENERAL = 7;
    const ANSWER = 8;
    const PAGE = 9;
    const COMPLEX_QUESTION = 10;
    const GROUP = 11;
    const CONTEXT = 12;
    const CONCLUSION = 13;
    const EXPRESSION = 14;
    const ACTION = 15;
    const MESSAGE = 16;

    static function ACTION() {
        return new EntityType(EntityType::ACTION);
    }

    static function ALGORITHM() {
        return new EntityType(EntityType::ALGORITHM);
    }

    static function QUESTION() {
        return new EntityType(EntityType::QUESTION);
    }

    static function RISK() {
        return new EntityType(EntityType::RISK);
    }

    static function RISK_GENERAL() {
        return new EntityType(EntityType::RISK_GENERAL);
    }

    static function DOCUMENT() {
        return new EntityType(EntityType::DOCUMENT);
    }

    static function DOCUMENT_GENERAL() {
        return new EntityType(EntityType::DOCUMENT_GENERAL);
    }

    static function INFO() {
        return new EntityType(EntityType::INFO);
    }

    static function ANSWER() {
        return new EntityType(EntityType::ANSWER);
    }

    static function PAGE() {
        return new EntityType(EntityType::PAGE);
    }

    static function COMPLEX_QUESTION() {
        return new EntityType(EntityType::COMPLEX_QUESTION);
    }

    static function GROUP() {
        return new EntityType(EntityType::GROUP);
    }

    static function CONTEXT() {
        return new EntityType(EntityType::CONTEXT);
    }

    static function CONCLUSION() {
        return new EntityType(EntityType::CONCLUSION);
    }

    static function EXPRESSION() {
        return new EntityType(EntityType::EXPRESSION);
    }

    static function MESSAGE() {
        return new EntityType(EntityType::MESSAGE);
    }


    public function id($id) {
        switch ($this->value) {
            case self::ACTION:
                return new ActionId($id);
            case self::ALGORITHM:
                return new AlgorithmId($id);
            case self::QUESTION:
                return new QuestionId($id);
            case self::RISK:
                return new RiskId($id);
            case self::DOCUMENT:
                return new DocumentId($id);
            case self::INFO:
                return new InfoId($id);
            case self::DOCUMENT_GENERAL:
                return new DocumentGeneralId($id);
            case self::RISK_GENERAL:
                return new RiskGeneralId($id);
            case self::ANSWER:
                return new AnswerId($id);
            case self::PAGE:
                return new PageId($id);
            case self::COMPLEX_QUESTION:
                return new ComplexQuestionId($id);
            case self::GROUP:
                return new GroupId($id);
            case self::CONTEXT:
                return new ContextId($id);
            case self::CONCLUSION:
                return new ConclusionId($id);
            case self::EXPRESSION:
                return new ExpressionId($id);
            case self::MESSAGE:
                return new MessageId($id);
        }
    }

    public function ids($ids) {
        switch ($this->value) {
            case self::ACTION:
                return new ActionIds($ids);
            case self::ALGORITHM:
                return new AlgorithmIds($ids);
            case self::QUESTION:
                return new QuestionIds($ids);
            case self::RISK:
                return new RiskIds($ids);
            case self::DOCUMENT:
                return new DocumentIds($ids);
            case self::INFO:
                return new InfoIds($ids);
            case self::DOCUMENT_GENERAL:
                return new DocumentGeneralIds($ids);
            case self::RISK_GENERAL:
                return new RiskGeneralIds($ids);
            case self::ANSWER:
                return new AnswerIds($ids);
            case self::COMPLEX_QUESTION:
                return new ComplexQuestionIds($ids);
            case self::GROUP:
                return new GroupIds($ids);
            case self::CONTEXT:
                return new ContextIds($ids);
            case self::CONCLUSION:
                return new ConclusionIds($ids);
            case self::EXPRESSION:
                return new ExpressionIds($ids);
            case self::MESSAGE:
                return new MessageIds($ids);
        }
    }

    public function name() {
        switch ($this->value) {
            case self::ACTION:
                return 'action';
            case self::ALGORITHM:
                return 'algorithm';
            case self::QUESTION:
                return 'question';
            case self::RISK:
                return 'risk';
            case self::DOCUMENT:
                return 'document';
            case self::INFO:
                return 'info';
            case self::DOCUMENT_GENERAL:
                return 'document_general';
            case self::RISK_GENERAL:
                return 'risk_general';
            case self::ANSWER:
                return 'answer';
            case self::COMPLEX_QUESTION:
                return 'complex_question';
            case self::GROUP:
                return 'group';
            case self::CONTEXT:
                return 'context';
            case self::CONCLUSION:
                return 'conclusion';
            case self::EXPRESSION:
                return 'expression';
            case self::MESSAGE:
                return 'message';
        }
    }

    protected static $set = array(
        self::ACTION,
        self::ALGORITHM,
        self::QUESTION,
        self::RISK,
        self::DOCUMENT,
        self::INFO,
        self::DOCUMENT_GENERAL,
        self::RISK_GENERAL,
        self::ANSWER,
        self::PAGE,
        self::COMPLEX_QUESTION,
        self::GROUP,
        self::CONTEXT,
        self::CONCLUSION,
        self::EXPRESSION,
        self::MESSAGE,
    );
}

/*
class TextType extends TP\Set {

    const BANK = 1;
    const PHYSICAL = 2;

    static function BANK() {
        return new TextType(TextType::BANK);
    }

    static function PHYSICAL() {
        return new TextType(TextType::PHYSICAL);
    }

    protected static $set = array(self::BANK, self::PHYSICAL);
}
*/


class RiskLevel extends TP\Set {

    const FATAL = 1;
    const CRITICAL = 2;
    const WARNING = 3;
    const INFO = 4;

    function name() {
        return $this->nameByValue($this->value);
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::FATAL: return 'Сделка не может быть совершена'; break;
            case self::CRITICAL: return 'Высокий риск'; break;
            case self::WARNING: return 'Средний риск'; break;
            case self::INFO: return 'Незначительный риск'; break;
        }
    }

    static  public function getFilterArray() {
        return array(
            self::FATAL => array('id' => self::FATAL, 'name' => self::nameByValue(self::FATAL)),
            self::CRITICAL => array('id' => self::CRITICAL, 'name' => self::nameByValue(self::CRITICAL)),
            self::WARNING => array('id' => self::WARNING, 'name' => self::nameByValue(self::WARNING)),
            self::INFO => array('id' => self::INFO, 'name' => self::nameByValue(self::INFO)),
        );
    }

    protected static $set = array(self::FATAL, self::CRITICAL, self::WARNING, self::INFO);
}



class QuestionType extends TP\Set {

    const YESNO = 'yesno';
    const COMMON = 'common';
    const DOC = 'doc';
    const COMPLEX = 'complex';

    function name() {
        return $this->nameByValue($this->value);
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::YESNO: return 'Да/Нет'; break;
            case self::COMMON: return 'Общий'; break;
            case self::DOC: return 'Документ'; break;
            case self::COMPLEX: return 'Составной'; break;
        }
    }

    static  public function getFilterArray() {
        return array(
            self::YESNO => array('id' => self::YESNO, 'name' => self::nameByValue(self::YESNO)),
            self::COMMON => array('id' => self::COMMON, 'name' => self::nameByValue(self::COMMON)),
            self::DOC => array('id' => self::DOC, 'name' => self::nameByValue(self::DOC)),
            self::COMPLEX => array('id' => self::COMPLEX, 'name' => self::nameByValue(self::COMPLEX)),
        );
    }

    protected static $set = array(self::YESNO, self::COMMON, self::DOC, self::COMPLEX);
}



class InfoType extends TP\Set {

    const TEXT = 'text';
    const LONG_TEXT = 'long_text';
    const THRESHOLD01 = 'threshold01';
    const THRESHOLD2 = 'threshold2';
    const THRESHOLD10 = 'threshold10';
    const THRESHOLD25_50 = 'threshold25_50';
    const FIO = 'fio';
    const DATE = 'date';
    const NUMBER = 'number';
    const MONEY = 'money';
    const PERIOD = 'period';
    const SQUARE = 'square';
    const PAYMENTS = 'payments';
    const EGRUL = 'egrul';
    const EXCEL = 'excel';
    const FILE = 'file';
    const FILE_EXL = 'file_exl';
    const HIDDEN = 'hidden';

    const INN = 'inn';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const URL = 'url';


    function name() {
        return $this->nameByValue($this->value);
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::TEXT: return 'Текст'; break;
            case self::LONG_TEXT: return 'Длинный текст'; break;
            case self::THRESHOLD01: return 'Сделка 0.1%'; break;
            case self::THRESHOLD2: return 'Сделка 2%'; break;
            case self::THRESHOLD10: return 'Сделка 10%'; break;
            case self::THRESHOLD25_50: return 'Сделка 25/50%'; break;
            case self::FIO: return 'Ф.И.О.'; break;
            case self::DATE: return 'Дата'; break;
            case self::NUMBER: return 'Число'; break;
            case self::MONEY: return 'Денежная сумма'; break;
            case self::PERIOD: return 'Срок';
            case self::SQUARE: return 'Площадь';
            case self::PAYMENTS: return 'Выплаты';
            case self::EGRUL: return 'Выписка ЕГРЮЛ';
            case self::EXCEL: return 'Выписка Excel';
            case self::FILE: return 'Файл';
            case self::FILE_EXL: return 'Файл Excel';
            case self::HIDDEN: return 'Скрытое поле';

            case self::INN: return 'ИНН';
            case self::PHONE: return 'Телефон';
            case self::EMAIL: return 'E-Mail';
            case self::URL: return 'URL';
        }
    }

    static  public function getFilterArray() {
        return array(
            self::TEXT => array('id' => self::TEXT, 'name' => self::nameByValue(self::TEXT)),
            self::LONG_TEXT => array('id' => self::LONG_TEXT, 'name' => self::nameByValue(self::LONG_TEXT)),
            self::THRESHOLD01 => array('id' => self::THRESHOLD01, 'name' => self::nameByValue(self::THRESHOLD01)),
            self::THRESHOLD2 => array('id' => self::THRESHOLD2, 'name' => self::nameByValue(self::THRESHOLD2)),
            self::THRESHOLD10 => array('id' => self::THRESHOLD10, 'name' => self::nameByValue(self::THRESHOLD10)),
            self::THRESHOLD25_50 => array('id' => self::THRESHOLD25_50, 'name' => self::nameByValue(self::THRESHOLD25_50)),
            self::FIO => array('id' => self::FIO, 'name' => self::nameByValue(self::FIO)),
            self::DATE => array('id' => self::DATE, 'name' => self::nameByValue(self::DATE)),
            self::NUMBER => array('id' => self::NUMBER, 'name' => self::nameByValue(self::NUMBER)),
            self::MONEY => array('id' => self::MONEY, 'name' => self::nameByValue(self::MONEY)),
            self::PERIOD => array('id' => self::PERIOD, 'name' => self::nameByValue(self::PERIOD)),
            self::SQUARE => array('id' => self::SQUARE, 'name' => self::nameByValue(self::SQUARE)),
         //   self::PAYMENTS => array('id' => self::PAYMENTS, 'name' => self::nameByValue(self::PAYMENTS)),
            self::EGRUL => array('id' => self::EGRUL, 'name' => self::nameByValue(self::EGRUL)),
            self::EXCEL => array('id' => self::EXCEL, 'name' => self::nameByValue(self::EXCEL)),
            self::FILE => array('id' => self::FILE, 'name' => self::nameByValue(self::FILE)),
            self::FILE_EXL => array('id' => self::FILE_EXL, 'name' => self::nameByValue(self::FILE_EXL)),
            self::HIDDEN => array('id' => self::HIDDEN, 'name' => self::nameByValue(self::HIDDEN)),
        //    self::INN => array('id' => self::INN, 'name' => self::nameByValue(self::INN)),
            self::PHONE => array('id' => self::PHONE, 'name' => self::nameByValue(self::PHONE)),
            self::EMAIL => array('id' => self::EMAIL, 'name' => self::nameByValue(self::EMAIL)),
            self::URL => array('id' => self::URL, 'name' => self::nameByValue(self::URL)),
        );
    }
    
    
    protected static $set = [
        self::TEXT, self::LONG_TEXT, self::THRESHOLD01, self::THRESHOLD2, self::THRESHOLD10, self::THRESHOLD25_50, self::FIO, self::DATE, self::NUMBER, self::MONEY, self::PERIOD,
        self::SQUARE, self::PAYMENTS, self::EGRUL, self::EXCEL, self::FILE, self::FILE_EXL, self::HIDDEN, self::INN, self::PHONE, self::EMAIL, self::URL
    ];

}


class ExpressionType extends TP\Set {

    const FORMULA = 'formula';
    const CONDITION = 'condition';
    const MANYVALUED = 'manyvalued';
    const VARIABLE = 'variable';

    function name() {
        return $this->nameByValue($this->value);
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::FORMULA: return 'Формула'; break;
            case self::CONDITION: return 'Условие'; break;
            case self::MANYVALUED: return 'Многозначный'; break;
            case self::VARIABLE: return 'Переменная'; break;
        }
    }

    static  public function getFilterArray() {
        return array(
            self::FORMULA => array('id' => self::FORMULA, 'name' => self::nameByValue(self::FORMULA)),
            self::CONDITION => array('id' => self::CONDITION, 'name' => self::nameByValue(self::CONDITION)),
            self::MANYVALUED => array('id' => self::MANYVALUED, 'name' => self::nameByValue(self::MANYVALUED)),
            self::VARIABLE => array('id' => self::VARIABLE, 'name' => self::nameByValue(self::VARIABLE)),
        );
    }

    protected static $set = array(self::FORMULA, self::CONDITION, self::MANYVALUED, self::VARIABLE);
}


class ActionType extends TP\Set {

    const SENDMAIL = 'sendmail';
    const SAVEFILE = 'savefile';
    const EGRUL = 'egrul';


    function name() {
        return $this->nameByValue($this->value);
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::SENDMAIL: return 'Отправка почты'; break;
            case self::SAVEFILE: return 'Сохранение файла'; break;
            case self::EGRUL: return 'Загрузка выписки ЕГРЮЛ'; break;
        }
    }

    static  public function getFilterArray() {
        return array(
            self::SENDMAIL => array('id' => self::SENDMAIL, 'name' => self::nameByValue(self::SENDMAIL)),
            self::SAVEFILE => array('id' => self::SAVEFILE, 'name' => self::nameByValue(self::SAVEFILE)),
            self::EGRUL => array('id' => self::EGRUL, 'name' => self::nameByValue(self::EGRUL)),
        );
    }

    protected static $set = array(self::SENDMAIL, self::SAVEFILE, self::EGRUL);
}

class ConclusionType extends TP\Set {
    const WORD = 'word';
    const EXCEL = 'excel';
    const TEXT = 'text';

    static  public function getFilterArray() {
        return array(
            self::TEXT => array('id' => self::TEXT, 'name' => self::nameByValue(self::TEXT)),
            self::EXCEL => array('id' => self::EXCEL, 'name' => self::nameByValue(self::EXCEL)),
            self::WORD => array('id' => self::WORD, 'name' => self::nameByValue(self::WORD)),
        );
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::TEXT: return 'Текст'; break;
            case self::EXCEL: return 'Файл Excel';
            case self::WORD: return 'Файл Word';
        }
    }

    protected static $set = [self::EXCEL, self::WORD, self::TEXT];
}


class MessageType extends TP\Set {

    const TEXT = 'text';
    const IMAGE = 'image';
    const IFRAME = 'iframe';
    const VIDEO = 'video';
    const CHART = 'chart';
    const FILE = 'file';


    function name() {
        return $this->nameByValue($this->value);
    }

    static function nameByValue($value) {
        switch ($value) {
            case self::TEXT: return 'Текст'; break;
            case self::IMAGE: return 'Изображение'; break;
            case self::IFRAME: return 'IFrame'; break;
            case self::VIDEO: return 'Видео'; break;
            case self::CHART: return 'График'; break;
            case self::FILE: return 'Файл'; break;
        }
    }

    static  public function getFilterArray() {
        return [
            self::TEXT => ['id' => self::TEXT, 'name' => self::nameByValue(self::TEXT)],
            self::IMAGE => ['id' => self::IMAGE, 'name' => self::nameByValue(self::IMAGE)],
            self::IFRAME => ['id' => self::IFRAME, 'name' => self::nameByValue(self::IFRAME)],
            self::VIDEO => ['id' => self::VIDEO, 'name' => self::nameByValue(self::VIDEO)],
            self::CHART => ['id' => self::CHART, 'name' => self::nameByValue(self::CHART)],
            self::FILE => ['id' => self::FILE, 'name' => self::nameByValue(self::FILE)],
        ];
    }

    protected static $set = [self::TEXT, self::IMAGE, self::IFRAME, self::VIDEO, self::CHART, self::FILE];
}