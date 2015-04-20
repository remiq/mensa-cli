<?php

class Mensa
{
    private $blacklist = array(
        // always
        'Antipasti', 'Salatbuffet',
        // side-carbs
        'Bratkartoffeln', 'Eierspätzle', 'Petersilienkartoffeln', 'Dampfkartoffeln',
        'Parboiledreis', 'Spiralnudeln', 'Reis', 'Vollkornspiralnudeln'
    );


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
        if (preg_match('/(S|s)teak/', $name)) return 'grill';
        if (preg_match('/((S|s)uppe|(B|b)rühe)/', $name)) return 'soup';
        if (preg_match('/((S|s)alat|Tofu|((S|s)oja))/', $name)) return 'veg';
        if (preg_match('/^(Brokkoli|Paprikagemüse)$/', $name)) return 'veg';
        if (preg_match('/((F|f)isch|(S|s)hrimp)/', $name)) return 'fish';

        // personal preferences
        if (preg_match('/Kohlrabi/', $name)) return '!';

        return '?';
    }
}
