<?php

namespace App\Acme;

use Afosto\Acme\Client as BaseClient;
use GuzzleHttp\Client as HttpClient;

/**
 * yaacのClientの拡張クラス
 * Acmeチャレンジの過程で行われる自サーバーへのアクセスを可能にするためのプロクシー設定機能を追加
 */
class Client extends BaseClient {

  /**
  * 親クラスのオーバーライド関数
  * Self test用のGuzzleクライアントを返す
  * 環境変数にproxyが設定されていればオプションに追加する
  * @return HttpClient
  */
  protected function getSelfTestClient()
  {
    $options = [
      'verify'          => false,
      'timeout'         => 10,
      'connect_timeout' => 3,
      'allow_redirects' => true,
    ];
    // Selftestがタイムアウトで失敗する場合proxyを利用する
    $proxy = config('app.acme_proxy');
    if ('' != $proxy) {
      $options['proxy'] = $proxy;
    }
    return new HttpClient($options);
  }
}
