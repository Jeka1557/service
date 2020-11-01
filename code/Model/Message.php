<?php


namespace Model;

use PT;

/**
 * Class Message
 *
 * @property-read $id
 * @property-read $header
 *
 */

class Message extends DictEntity {

    protected $_id;
    protected $_header;

    protected $_text;
    protected $_textHTML = true;
    protected $_textHeader;

    protected $_hidden = false;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Message $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\MessageId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        $entity->_hidden = static::castVar($data['hidden'],'TP\TBool');

        $entity->_entityType = PT\EntityType::MESSAGE();

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }


    /**
     * @param PT\MessageType $type
     * @param $data
     * @return Message|null
     * @throws \Exception
     */

    static public function newEntity(PT\MessageType $type, $data) {

        $data['infoType'] = $type;

        switch ($type->val()) {
            case PT\MessageType::TEXT:
                return \Model\Message\Text::newFromArray($data);

            case PT\MessageType::IFRAME:
                return \Model\Message\IFrame::newFromArray($data);

            case PT\MessageType::CHART:
                return \Model\Message\Chart::newFromArray($data);

            case PT\MessageType::IMAGE:
                return \Model\Message\Image::newFromArray($data);

            case PT\MessageType::VIDEO:
                return \Model\Message\Video::newFromArray($data);

            case PT\MessageType::FILE:
                return \Model\Message\File::newFromArray($data);

            default:
                throw new \Exception("Storage: unknown message type");
        }
    }


    public function render($inGroup = false) {
        if (self::$renderMode==self::RENDER_MODE_ARRAY) {
            /** @todo Доделать, проработать что нужно возвращать */
            $result = [
                'text' => $this->text,
            ];

            return $result;

        } elseif (self::$renderMode==self::RENDER_MODE_BST4) {
            return $this->renderTemplate('Message', $this->TMPL_BST4, [
                'message' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            return $this->renderTemplate('Message', $this->TMPL_WC, [
                'message' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_VTB) {
            return $this->renderTemplate('Message', $this->TMPL_VTB, [
                'message' => $this,
                'inGroup' => $inGroup,
            ]);

        } else {
            return $this->renderTemplate('Message', $this->TMPL_DEFAULT, [
                'message' => $this,
                'inGroup' => $inGroup,
            ]);
        }
    }


    protected function applyContext($contextData) {
        $context = parent::applyContext($contextData);

        if (!is_null($context)) {
            $this->_textHTML = $context['html'];
            $this->_textHeader = $context['header'];
        }

        return $context;
    }
}