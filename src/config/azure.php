<?php
return [

    'client_id'=>env('AZURE_CLIENT_ID',''),


    'client_secret'=>env('AZURE_CLIENT_SECRET',''),


    'redirect_uri'=>env('AZURE_REDIRECT_URI',''),


    'graph_api_url_resource'=>env('AZURE_RESOURCE',''),


    'tenant_id'=>env('AZURE_TENANT_ID',''),


    'use_ssl' =>  env('AZURE_USE_SSL',false) ,


    'azure_response_mode'=>env('AZURE_RESPONSE_MODE',''),


    'azure_log_out_url'=>env('AZURE_LOG_OUT_URL',''),


];
