<?php

use Symfony\Component\Yaml\Yaml;

return Yaml::parse(file_get_contents(__DIR__.'/constants.yml'));
