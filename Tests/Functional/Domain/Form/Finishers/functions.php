<?php

declare(strict_types = 1);

namespace TYPO3\CMS\Core\Resource;

// Override for ResourceStorage::assureFileUploadPermissions()
function is_uploaded_file(string $filename): bool
{
    return true;
};
