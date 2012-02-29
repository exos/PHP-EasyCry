<?php

/* EJEMPLO DE USO DE EasyCry con un archivo como llave, esto es mas seguro que una contraseña */

require ("../lib/EasyCry.php");

$oCry = new EasyCry();

// Comprimimos usando configuracion por defecto:

echo "\nencriptando:\n";

$lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.Ut erat libero, condimentum nec volutpat sed, lobortis ut ante. Aenean aliquam vehicula nisi et tempor. Morbi commodo justo tincidunt tortor malesuada vitae laoreet augue vehicula. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In gravida, libero eget elementum rutrum, ipsum lorem condimentum purus, eu pharetra ligula massa nec magna. Sed accumsan massa in odio tristique id eleifend purus pulvinar. Fusce ornare, orci et interdum rhoncus, enim lectus accumsan odio, auctor blandit arcu diam et erat. Donec eleifend luctus porta. Vestibulum et facilisis neque. Nam sed ipsum sed felis aliquet volutpat sed id nisi. Praesent sollicitudin aliquam nulla eu viverra. Quisque semper convallis pharetra. Proin ac sapien massa. Pellentesque venenatis diam in nibh iaculis quis porta felis blandit. Suspendisse porttitor ultricies nunc, vel tempus mauris vestibulum vitae. Praesent dolor nibh, dictum ac volutpat et, pretium ac sapien.";

// Cargamos el arhivo a usar como llave:
$key = file_get_contents("key.bin");

// Encriptamos usando "clave" de password
$enc = $oCry->encode($lorem,$key);

echo "Lorem Ipsum encriptado:	\n\n" . wordwrap($enc,60,"\n",true) ."\n\n";

// Des-encriptamos usando la misma clave "clave"
$enc = $oCry->decode($enc,$key);

echo "Desencriptando :\n\n {$enc['data']}\n";

