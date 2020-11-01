<?php


namespace Model;
use Lib\Model\Entity;
use Infr;
use PT;
use Model\Exception;

/**
 * @property array $contextTexts
 * @property-read $id
 * @property-read $extId
 */

class DictEntity extends Entity {

    const RENDER_MODE_HTML = 1;
    const RENDER_MODE_ARRAY = 2;
    const RENDER_MODE_BST4 = 3; // HTML for Bootstrap 4
    const RENDER_MODE_WC = 4; // HTML for Bootstrap 4
    const RENDER_MODE_VTB = 5; // HTML for VTB



    const TMPL_ROOT = SERVICE_TMPL_ROOT;


    static protected $contextId = PT\ContextId::EMPTY_ID;

    static protected $extraContextIds = array();

    static protected $renderMode = self::RENDER_MODE_HTML;

    protected $_text;
    protected $_id;

    protected $_extId;

    protected $_contextTexts = array();

    protected $_algIds = [];


    /**
     * @var \PT\EntityType
     */
    protected $_entityType;

    static function extId($id, $copyId) {
        return "{$id}".($copyId>0?"_{$copyId}":'');
    }

    static function setRenderMode($mode) {
        self::$renderMode = $mode;
    }

    static function setContext($contextId) {
        self::$contextId = $contextId;
    }

    static function getContext() {
        return self::$contextId;
    }

    static function setExtraContexts($contextIds) {
        self::$extraContextIds = array();

        foreach ($contextIds as $id) {
            self::$extraContextIds[] = (int)$id;
        }

        self::$extraContextIds = array_unique(self::$extraContextIds);
    }

    public function setAlgorithmIds($ids) {
        $this->_algIds = $ids;
    }

    /**
     * @param $contextData
     * @return mixed|null
     * @throws Exception\ContextNotFoundException
     * @throws Exception\NotExistsInContextsException
     */

    protected function applyContext($contextData) {
        if (is_array($contextData) and !count($contextData))
            return null;

        if (static::$contextId==PT\ContextId::EMPTY_ID)
            return null;

        if (isset($contextData[static::$contextId])) {
            $context = $contextData[static::$contextId];

        } elseif (isset($contextData[PT\ContextId::DEFAULT_ID])) {
            $context = $contextData[PT\ContextId::DEFAULT_ID];

        } else {
            throw new Exception\ContextNotFoundException($this->_entityType, $this->_id, static::$contextId);
        }

        if ($context['notExists'])
            throw new Exception\NotExistsInContextsException("");

        $this->_text = LinkRenderer::instance()->renderLinks($context['text']);
        $this->applyExtraContexts($contextData);

        return $context;
    }


    protected function applyExtraContexts($contextData) {
        foreach (static::$extraContextIds as $contextId) {
            if (isset($contextData[$contextId])) {
                $this->_contextTexts[$contextId] = LinkRenderer::instance()->renderLinks($contextData[$contextId]['text']);
            } else {
                $this->_contextTexts[$contextId] = '';
            }
        }
    }


    public function render($inGroup = false) {
        if (self::$renderMode==self::RENDER_MODE_ARRAY) {
            $result = [
                'text' => $this->text,
            ];

            return $result;

         } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            return $this->renderTemplate('Entity', 'wc/Text', [
                'entity' => $this,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_VTB) {
            return $this->renderTemplate('Entity', 'vtb/Text', [
                'entity' => $this,
            ]);

        } else {
            return $this->renderTemplate('Entity', 'Text', [
                'entity' => $this,
            ]);
        }
    }



    /**
     * Преобразование объекта к массиву.
     *
     * @param array|null $varsArray список свойств сущности, которые необходимо преобразовывать в массив
     * @return array
     * @access public
     */
    public function toArray(array $varsArray = null) {
        $data = array();
        $vars = get_object_vars($this);

        foreach ($vars as $m => $v) {
            if ($m[0] != '_') {
                continue;
            }

            if (!isset($this->$m)) {
                continue;
            }

            $key = substr($m, 1);

            if ($varsArray && (!in_array($key, $varsArray) and !array_key_exists($key, $varsArray))) {
                continue;
            }

            $v = $this->$key;

            if (is_object($v)) {
                if (method_exists($v, 'toArray')) {
                    /* @var \TP\Arr\Arr|\Lib\Model\Value|\Lib\Model\Collection $v */
                    $v = $v->toArray(isset($varsArray[$key])?$varsArray[$key]:null);

                } elseif ($v instanceof \TP\Type) {
                    /* @var \TP\Type $v */
                    $v = $v->val();

                } else {
                    $v = strval($v);
                }
            }

            $data[$key] = $v;
        }

        return $data;
    }


    public function export() {

        $data = [
            'id' => $this->_id,
            'text' => $this->_text,
            'extId' => $this->_extId,
            'algIds' => $this->_algIds,
        ];

        return $data;
    }


    protected function renderTemplate($block, $template, $variables) {
        extract($variables);
        ob_start();
        require(static::TMPL_ROOT."/Model/{$block}/{$template}.phtml");

        return ob_get_clean();
    }

}
