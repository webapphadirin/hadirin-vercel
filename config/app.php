<?php
return ['name'=>env('APP_NAME','HADIRIN'),'env'=>env('APP_ENV','production'),'debug'=>(bool) env('APP_DEBUG',false),'url'=>env('APP_URL','http://localhost'),'timezone'=>env('APP_TIMEZONE','Asia/Jakarta'),'locale'=>env('APP_LOCALE','id'),'fallback_locale'=>'en','faker_locale'=>'id_ID','key'=>env('APP_KEY'),'cipher'=>'AES-256-CBC','maintenance'=>['driver'=>'file']];
