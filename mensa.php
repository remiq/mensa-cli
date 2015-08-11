<?php

class Mensa
{
    private $blacklist = array();
    private $favs = array();

    public function __construct() {
        if (file_exists('blacklist.txt'))
            $this->blacklist = array_filter(preg_split('/\n/', file_get_contents('blacklist.txt')));
        if (file_exists('favs.txt'))
            $this->favs = array_filter(preg_split('/\n/', file_get_contents('favs.txt')));
    }


    private function get_dom($date)
    {
        $daily_file = realpath(dirname(__FILE__)).'/mensa-'.$date.'.html';
        if (!file_exists($daily_file)) {
            // TODO: this does not download selected date, only current
            $file_content = file_get_contents('http://www.studentenwerk-berlin.de/cn/mensen/speiseplan/hu_nord/');
            file_put_contents($daily_file, $file_content);
        }

        libxml_use_internal_errors(true);
        $dom = new DomDocument;
        $dom->loadHTMLFile($daily_file);
        return $dom;
    }

    public function parse($date)
    {
        $ret = array();
        $xpath = new DOMXPath($this->get_dom($date));
        $nodes = $xpath->query('//td[@class="mensa_day_speise_name"]');
        foreach ($nodes as $i => $node) {
            $class = $node->childNodes->item(1)->attributes->getNamedItem('href')->textContent;
            list($nvm, $color) = preg_split('/_/', $class);
            $name = self::parse_name($node->nodeValue);
            if (in_array($name, $this->blacklist)) continue;
            $ret[] = array(
                'name'  =>  $name,
                'type'  =>  $this->get_type($name),
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

    private function get_type($name)
    {
        // personal preferences
        $fav_regex = '/'.join('|', $this->favs).'/';
        if (preg_match($fav_regex, $name)) return '!';

        if (preg_match('/steak|currywurst/i', $name)) return 'grill';
        if (preg_match('/(fisch|shrimp|filet|hering)/i', $name)) return 'fish';
        if (preg_match('/(suppe|brühe|bouillon)/i', $name)) return 'soup';
        if (preg_match('/(salat|tofu|soja|brokkoli|gemüse|bohnen|zucchini|vegetar)/i', $name)) return 'veg';

        return '?';
    }
}
