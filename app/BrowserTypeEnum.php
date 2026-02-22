<?php

namespace App;

enum BrowserTypeEnum: string
{
    case CHROME='chrome';
    case FIREFOX='firefox';
    case SAFARI='safari';
    case ALL='all';
}
