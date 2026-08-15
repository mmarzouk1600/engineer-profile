<?php

return [
    'allowed_file_extensions' => explode(',', env('ALBUM_ALLOWED_FILE_EXTENSIONS', 'pdf,dwg,dxf,zip,doc,docx,xls,xlsx')),
    'allowed_file_mimes' => explode(',', env('ALBUM_ALLOWED_FILE_MIMES', 'application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/acad,image/vnd.dwg,image/x-dwg,application/octet-stream')),
    'max_image_size_kb' => (int) env('ALBUM_MAX_IMAGE_SIZE_KB', 10240),
    'max_file_size_kb' => (int) env('ALBUM_MAX_FILE_SIZE_KB', 102400),
    'image_disk' => env('ALBUM_IMAGE_DISK', 'public'),
    'file_disk' => env('ALBUM_FILE_DISK', 'local'),
    'image_path' => 'albums/images',
    'file_path' => 'albums/files',
    'thumbnail_width' => (int) env('ALBUM_THUMBNAIL_WIDTH', 600),
    'default_currency' => env('ALBUM_DEFAULT_CURRENCY', 'SAR'),
    'per_page' => (int) env('ALBUM_PER_PAGE', 12),
];
