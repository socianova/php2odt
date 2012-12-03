<?php

/**
 * Description of parseHtml
 * 
 * Date - $Date: 2012-12-03 13:00:00 +0200 (lun., 03 décembre 2012) $
 * @author Sophien
 */

 /*
 * @copyright  GPL License 2012 - Mehboub Sophien - sociaNova (http://www.socianova.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.fr.html  GPL License
 * @version 1.0
 */
class parseHtml {

    private $array = array();
    public $i = 1;

    private function functTest($noeud) {
        foreach ($noeud->child as $value) {
            if ($value->name != '') {
                $attributes = array();
                if (isset($value->attribute)) {
                    foreach ($value->attribute as $key => $val) {
                        $attributes[$key] = $val;
                    }
                }
                $this->{'array' . $this->i}[$value->name] = $attributes;
                if ($value->hasChildren()) {
                    $this->functTest($value);
                }
            }
        }
    }

    public function get_nodes($node) {
        if ($node->isHtml()) {
            if (!in_array($node->name, array('html', 'head', 'title', 'body', ''))) {
                if (($node->child[0]->child != NULL) and (in_array($node->getparent()->name, array('html', 'head', 'title', 'body', '')))) {
                    $attributes = array();
                    if (isset($node->attribute)) {
                        foreach ($node->attribute as $key => $value) {
                            $attributes[$key] = $value;
                        }
                    }
                    $this->{'array' . $this->i}[$node->name] = $attributes;
                    $this->functTest($node);
                    $this->array[] = $this->{'array' . $this->i};
                    $this->i++;
                } else if ((in_array($node->getparent()->name, array('html', 'head', 'title', 'body', '')))) {
                    $attributes = array();
                    if (isset($node->attribute)) {
                        foreach ($node->attribute as $key => $value) {
                            $attributes[$key] = $value;
                        }
                    }
                    $array = array($node->name => $attributes);
                    $this->array[] = $array;
                }
            }
        }

        if ($node->hasChildren()) {
            foreach ($node->child as $child) {
                $this->get_nodes($child);
            }
        }
    }

    public function getArray() {
        return $this->array;
    }

}

?>
