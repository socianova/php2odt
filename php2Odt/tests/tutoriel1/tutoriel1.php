<?php

/**
 * Description of tutoriel1
 *
 * @author sophien
 */
include_once('../../OdtLib/php2Odt.php');

$t = array(
    'textSegment1' => array(
    'var1',
    'var2',
    'var3',
    'var4'),
    'textSegment2' => array(
    'var1',
    'var2',
    'var3',
    'var4'
    ));


$odf = new php2Odt('tutoriel1.odt');

$odf->setVar('titre', 'valeur');

$array = array('image' => array(
        'file' => './sociaNOVA.jpg',
        'align' => 'center',
        'width' => 14,
        'height' => 3)
);

$odf->setImage($array);

$segment = $odf->setSegment('Segment');

$segment->setvar($t);

$odf->exportAsAttachedFile();
?>
