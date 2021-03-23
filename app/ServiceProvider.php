<?php

namespace App;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;
use Vimeo\Vimeo;

/**
 * Class ServiceProvider.
 *
 * @package App
 */
class ServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(Container $container) {
    $container['config'] = function ($c) {
      $config['docroot'] = DOCROOT;
      $config = Yaml::parseFile(DOCROOT . '/config.yml');

      return $config;
    };

    $container['vimeo.client'] = function ($c) {
      $client_id = $c['config']['vimeo']['client_id'];
      $client_secret = $c['config']['vimeo']['client_secret'];
      $access_token = $c['config']['vimeo']['access_token'];
      return new Vimeo($client_id, $client_secret, $access_token);
    };

//    $container['data.api'] = function ($c) {
//      return new ProxyApi($c['react.loop'], $c['data.api.route_collection'], $c['data.gxp_resolver']);
//    };
//
//    $container['data.api.route_collection'] = function ($c) {
//      return new RouteCollector(new StdRouteParser(), new GroupCountBased());
//    };
  }

}
