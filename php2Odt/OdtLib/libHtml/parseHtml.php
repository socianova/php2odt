<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author sophien
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
        // Vérifie si le noeud courant est du type demandé
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

        // Vérifie si le noeud courant a des enfants
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
