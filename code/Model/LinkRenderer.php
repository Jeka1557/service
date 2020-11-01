<?php


namespace Model;
use PT;
use Lib\Infr\DSN;
use Lib\Infr\Db\Adapter;


class LinkRenderer  {

    const MODE_OFF = 0;
    const MODE_WEB = 1;
    const MODE_CONSULTANT = 2;
    const MODE_GARANT = 3;

    /**
     * @var LinkRenderer
     */
    static protected $instance;

    protected $linkMarker = '\/link\?';

    /**
     * @var Adapter
     */
    protected $dbAdapter;
    /**
     * @var DSN
     */
    protected $dsn;

    protected $mode = self::MODE_WEB;

    protected $links = array();
    protected $unloadedLinks = array();

    protected function __construct(DSN $dsn) {
        $this->dsn = $dsn;
        $this->dbAdapter = Adapter::create($dsn::DRIVER, $dsn);
    }

    /**
     * @param DSN $dsn
     * @return LinkRenderer
     */

    static public  function instance(DSN $dsn = null) {
        if (is_null(self::$instance) and !is_null($dsn))
            self::$instance = new LinkRenderer($dsn);

        return self::$instance;
    }


    public function setMode($mode) {
        $this->mode = $mode;
    }


    public function renderLinks($text) {
        if ($this->mode==self::MODE_OFF)
            return $text;

        $this->extractLinkIds($text);
        $this->loadLinks();

        $renderer = $this;
        $text = preg_replace_callback("~(<a[^>]+href=([\"']))({$this->linkMarker}(\d+))\\2~",

            function ($matches) use ($renderer) {
                $linkId = (int)$matches[4];

                $url = $renderer->getLinkUrl($linkId);

                if (!is_null($url)) {
                    return $matches[1].$url.$matches[2].' target=_blank';
                } else
                    return $matches[1].'#'.$matches[2];

            },
            $text);

        $text = preg_replace_callback("~(<img[^>]+src=([\"']))({$this->linkMarker}(\d+))\\2~",

            function ($matches) use ($renderer) {
                $linkId = (int)$matches[4];

                $url = $renderer->getLinkUrl($linkId);

                return $matches[1].(is_null($url)?'':$url).$matches[2];
            },
            $text);

        return $text;
    }

    public function getLinkUrl($id) {
        if (!isset($this->links[$id]))
            return null;

        $link = $this->links[$id];

        switch ($this->mode) {
            case self::MODE_GARANT:
                $url = empty($link['garant'])?$link['web']:$link['garant'];
            break;

            case self::MODE_CONSULTANT:
                $url = empty($link['consultant'])?$link['web']:$link['consultant'];
            break;

            case self::MODE_WEB:
            default:
                $url = $link['web'];
        }

        return empty($url)?null:$url;
    }

    protected function extractLinkIds($text) {
        $matches = array();
        preg_match_all("~<a[^>]+href=([\"']){$this->linkMarker}(\d+)\\1~", $text, $matches);

        foreach ($matches[2] as $linkId) {
            $linkId = (int)$linkId;

            if (!$linkId>0)
                throw new \Exception("Invalid link id: {$linkId}");

            if (!isset($this->links[$linkId]))
                $this->unloadedLinks[$linkId] = $linkId;
        }

        preg_match_all("~<img[^>]+src=([\"']){$this->linkMarker}(\d+)\\1~", $text, $matches);

        foreach ($matches[2] as $linkId) {
            $linkId = (int)$linkId;

            if (!$linkId>0)
                throw new \Exception("Invalid link id: {$linkId}");

            if (!isset($this->links[$linkId]))
                $this->unloadedLinks[$linkId] = $linkId;
        }
    }

    public  function loadLink($id) {
        if (!isset($this->links[$id])) {
            $this->unloadedLinks[$id] = $id;
            $this->loadLinks();
        }
    }


    protected function loadLinks() {

        if (!count($this->unloadedLinks))
            return;

        $select = new \Infr\Db\Content\Node($this->dsn);
        $rows = $select->where("id", array_keys($this->unloadedLinks))
            ->execute()
            ->fetchAll();

        foreach ($rows as $row) {
            $linkId = (int)$row['id'];

            if (!isset($this->links[$linkId]))
                $this->links[$linkId] = array(
                    'web' => $row['url_web'],
                    'consultant' => $row['url_consultant'],
                    'garant' => $row['url_garant'],
                );

            unset($this->unloadedLinks[$linkId]);
        }
    }

}