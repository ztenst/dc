#!/usr/bin/env bash
echo yes|/usr/local/php/bin/php yii crontab/refresh-stock
echo yes|/usr/local/php/bin/php yii crontab/statistics-sale-price
echo yes|/usr/local/php/bin/php yii crontab/statistics-menu-sale
echo yes|/usr/local/php/bin/php yii crontab/statistics-desk-use
echo yes|/usr/local/php/bin/php yii crontab/statistics-desk-count
