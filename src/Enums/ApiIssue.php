<?php

namespace romanzipp\MailCheck\Enums;

enum ApiIssue: int
{
    case ALLOW = 0;
    case DENY = 1;
    case EXCEPTION = 2;
}
