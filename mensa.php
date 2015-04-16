<?php

class Mensa
{
    private $blacklist = array(
        'Antipasti', 'Salatbuffet',
        'Bratkartoffeln', 'Eierspätzle', 'Petersilienkartoffeln'
    );

    private $dom;

    public function init()
    {
        $daily_file = realpath(dirname(__FILE__)).'mensa-'.date('Ymd').'.html';
        if (!file_exists($daily_file)) {
            $file_content = file_get_contents('http://www.studentenwerk-berlin.de/cn/mensen/speiseplan/hu_nord/');
            file_put_contents($daily_file, $file_content);
        }

        libxml_use_internal_errors(true);
        $this->dom = new DomDocument;
        $this->dom->loadHTMLFile($daily_file);
    }

    public function parse()
    {
        $ret = array();
        $xpath = new DOMXPath($this->dom);
        $nodes = $xpath->query('//td[@class="mensa_day_speise_name"]');
        foreach ($nodes as $i => $node) {
            $class = $node->childNodes->item(1)->attributes->getNamedItem('href')->textContent;
            list($nvm, $color) = preg_split('/_/', $class);
            $name = self::parse_name($node->nodeValue);
            if (in_array($name, $this->blacklist)) continue;
            $ret[] = array(
                'name'  =>  $name,
                'type'  =>  self::get_type($name),
                'color' =>  $color,
            );
        }
        return $ret;
    }

    private static function parse_name($name) {
        $name = trim($name);
        $name = preg_replace('/ /', '_', $name);
        $name = preg_replace('/\s/', '', $name);
        $name = preg_replace('/[0-9]/', '', $name);
        $name = preg_replace('/_/', ' ', $name);
        $name = trim($name);
        return $name;
    }

    private static function get_type($name)
    {
        if (preg_match('/((S|s)uppe|brühe)/', $name)) return 'soup';
        if (preg_match('/(Salat)/', $name)) return 'veg';
        if (preg_match('/^(Brokkoli|Paprikagemüse)$/', $name)) return 'veg';
        return '?';
    }
}
