#!/usr/bin/env bash

composer dump-autoload -o
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
