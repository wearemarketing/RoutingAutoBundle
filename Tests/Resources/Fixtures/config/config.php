<?php

$container->loadFromExtension('cmf_routing_auto', array(
    'auto_mapping' => false,
    'mapping' => array(
        'paths' => array(
            'Resources/config/SpecificObject.yml',
            array('path' => 'Document/Post.php', 'type' => 'annotation'),
            array('path' => 'Resources/config/foo.xml'),
        ),
    ),
));
