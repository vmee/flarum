<?php

namespace Flagrow\Bazaar\Listeners;

use Flagrow\Bazaar\Composer\ComposerEnvironment;
use Flagrow\Bazaar\Extensions\PackageManager;
use Flagrow\Bazaar\Search\FlagrowApi;
use Flagrow\Bazaar\Traits\FileSizeHelper;
use Flarum\Event\PrepareUnserializedSettings;
use Illuminate\Events\Dispatcher;

class AddApiAttributes
{
    use FileSizeHelper;

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PrepareUnserializedSettings::class, [$this, 'addAdminAttributes']);
    }

    /**
     * @param PrepareUnserializedSettings $event
     */
    public function addAdminAttributes(PrepareUnserializedSettings $event)
    {
        $event->settings['flagrow.bazaar.flagrow-host'] = FlagrowApi::getFlagrowHost();
        $event->settings['flagrow.bazaar.php.memory_limit-met'] = $this->memoryLimitMet();
        $event->settings['flagrow.bazaar.php.memory_limit'] = ini_get('memory_limit');
        $event->settings['flagrow.bazaar.php.memory_requested'] = PackageManager::MEMORY_REQUESTED;
        $event->settings['flagrow.bazaar.file-permissions'] = $this->retrieveFilePermissions();
    }

    protected function retrieveFilePermissions()
    {
        return app(ComposerEnvironment::class)->retrieveFilePermissions();
    }

    protected function memoryLimitMet()
    {
        $limit = ini_get('memory_limit');

        if ($limit === -1) {
            return true;
        }

        $required = $this->sizeToByte(PackageManager::MEMORY_REQUESTED);

        return $this->sizeToByte($limit) >= $required;
    }
}
