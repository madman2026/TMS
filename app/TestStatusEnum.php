<?php

namespace App;

enum TestStatusEnum:string
{
    case PENDING='pending';
    case FINISHED='finished';
    case FAILED='failed';
}
