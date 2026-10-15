<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Selectors;

use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;

final class OperationsHealthSelectors
{
    public const SURFACE = 'notification-delivery.operations-health';

    public const OBSERVATION = 'notification-delivery.operations-health.v1';

    public const NAVIGATION_ROOT = '#cms-plugins-notification-delivery';

    public const NAVIGATION_GROUP = '[data-nd-menu-section="cms-plugins-notification-delivery-monitoring-governance"]';

    public const NAVIGATION_ITEM = '#cms-plugins-notification-delivery-health';

    public const FOCUS_TARGET = '#nd-contextual-help-provider-health';

    public const DOCUMENT_MAIN = 'main';

    public static function document(OperationsHealthVariant $variant): string
    {
        $direction = $variant->direction === 'rtl' ? '[dir="rtl"]' : ':not([dir="rtl"])';
        $theme = $variant->theme === 'dark' ? '[data-bs-theme="dark"]' : ':not([data-bs-theme="dark"])';

        return sprintf('html[lang="%s"] body%s%s', $variant->locale, $direction, $theme);
    }
}
