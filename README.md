EasyCry
=======

Fast simetric encription method write in PHP

This class encript any content (plain text and binary) using a ilimitate size key.

You can use a password phrase or binary file to encode/decode content.

Ho use it?
----------

The use is very easy

```php
<?php

// Require the class
require ("lib/EasyCry.php");

// Create an object
$oCry = new EasyCry();


$lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.Ut erat libero, condimentum nec volutpat sed, lobortis ut ante. Aenean aliquam vehicula nisi et tempor. Morbi commodo justo tincidunt tortor malesuada vitae laoreet augue vehicula. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In gravida, libero eget elementum rutrum, ipsum lorem condimentum purus, eu pharetra ligula massa nec magna. Sed accumsan massa in odio tristique id eleifend purus pulvinar. Fusce ornare, orci et interdum rhoncus, enim lectus accumsan odio, auctor blandit arcu diam et erat. Donec eleifend luctus porta. Vestibulum et facilisis neque. Nam sed ipsum sed felis aliquet volutpat sed id nisi. Praesent sollicitudin aliquam nulla eu viverra. Quisque semper convallis pharetra. Proin ac sapien massa. Pellentesque venenatis diam in nibh iaculis quis porta felis blandit. Suspendisse porttitor ultricies nunc, vel tempus mauris vestibulum vitae. Praesent dolor nibh, dictum ac volutpat et, pretium ac sapien.";

// Encrypt with password phrase 'simpe_pass'
$enc = $oCry->encode($lorem,'simple_pass');

// The $lorem is encripted and encode to base64 
echo wordwrap($enc,60,"\n",true) ."\n\n";

// Decrypt encrypten content with the same password phrase
$enc = $oCry->decode($enc,"clave");

// See the original lorem
echo {$enc['data']}\n\n";
```

Using a key file
----------------

You can use a random and binary byte data to encrypt a message, in linux you can do:

```
$ dd if=/dev/random of=keyfile.bin bs=1024 size=4
```

This generate a 4KB binary file with random content

```php
<?php

$key = file_get_contents('keyfile.bin');

$oCry->encode($lorem, $key);
```

Use for passwords encryption?
-----------------------------

No recomended, use an irrevertible hash algoritsm like md5, sha1, sha256, etc

I wanna crack it!
-----------------

You're welcome:

- http://www.kriptopolis.org/node/7717

```
Njg0NTU2NmE1OTM4YTVmZTEzZjYyOGU3MTFjOWNmYmUzNjE2ZTFhZThkMmZk
NzQ4YWY0YmQyYzMwODkxZmQxNWqVm22nmZSuVlqtb6xybGpna8jKa5tUnnKb
c5Vtp1lvbqWGa5NZbGXVaIdbZmydm6Geo1qek5hbc59pmciWZ1Vrl5aSZWmc
ZMmXaZ2XlJpum5fGlZ5yk51jhrZjnZVnlMjHkWeUm5vIxWpim5qUk5icamhu
k5ltlpvHZmxpxpaWvqpxgWSzoPZtEP7tiuVUKEzYZpLvJooRsn7iZrK2S6Tz
vHI8ibObYh9bKHzaJDVbFfOJhTSeM+Z/uCXdRErmXAt9OYGaiVbcG2Kh8Xi2
wsOwkjxPMuVVzpoGJwjcEeygJUQgrLr730hmdpHO20FmHdVqnNWlkz1btK0o
4reOeuYRlFFGRmf1wGpspcLVnVfAszmaLBssdF5VxIkuaUq9sN12QDdWeSAN
jk+Or8Fk23xadkzCqzoR3gjK85opFlsdpLns+KebedTgkzdTjsvTOixV42VY
FnNYStIyU+8hWq+P51BJFCVnMbFi2+7JJ8rF9VZUXFFjbr1iAqX3FjOX6o+c
bBMx3DkO
``` 
