<?php

namespace App\Enums;

enum EnvironmentEnum: string
{
    case production = 'production';


    case local = 'local';

    case testing = 'testing';

}
