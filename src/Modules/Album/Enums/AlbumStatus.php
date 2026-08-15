<?php

namespace Modules\Album\Enums;

enum AlbumStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
