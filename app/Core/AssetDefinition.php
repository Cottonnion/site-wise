<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

class AssetDefinition
{
    public function __construct(
        public readonly string $handle,
        public readonly string $file,
        public readonly array $deps = [],
        public readonly array $localize = [],
        public readonly string $version = WSAL_VERSION,
        public readonly bool $in_footer = true,
        public readonly ?string $base_url = null,
    ) {}
}