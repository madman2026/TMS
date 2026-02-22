<?php

namespace App;

enum DriverTypeEnum: string
{
    case CHROME='chrome';
    case FIREFOX='firefox';
    case SAFARI='safari';
    case ALL='all';
}
