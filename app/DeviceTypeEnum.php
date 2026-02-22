<?php

namespace App;

enum DeviceTypeEnum: string
{
    case MOBILE='mobile';
    case DESKTOP='desktop';
    case TABLET='tablet';
    case ALL='all';
}
