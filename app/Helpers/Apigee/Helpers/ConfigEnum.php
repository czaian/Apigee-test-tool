<?php

namespace App\Helpers\Apigee\Helpers;

enum ConfigEnum: string
{
    case ConfigOriginKey = 'from';
    case ConfigTargetKey = 'to';

    case BundleFormat = 'bundle';

}
