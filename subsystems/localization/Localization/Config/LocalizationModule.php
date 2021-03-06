<?php
namespace Electro\Localization\Config;

use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\KernelInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Localization\Services\Locale;
use Electro\Localization\Services\TranslationService;
use Electro\Profiles\ApiProfile;
use Electro\Profiles\ConsoleProfile;
use Electro\Profiles\WebProfile;

class LocalizationModule implements ModuleInterface
{
  static function getCompatibleProfiles ()
  {
    return [WebProfile::class, ConsoleProfile::class, ApiProfile::class];
  }

  static function startUp (KernelInterface $kernel, ModuleInfo $moduleInfo)
  {
    $kernel
      ->onRegisterServices (
        function (InjectorInterface $injector) {
          $injector
            ->share (LocalizationSettings::class)
            ->share (new Locale, 'locale')
            ->share (TranslationService::class, 'trans');
        })
      //
      ->onConfigure (
        function (LocalizationSettings $settings) {
          if ($tz = $settings->timeZone ())
            date_default_timezone_set ($tz);
        });
  }

}
