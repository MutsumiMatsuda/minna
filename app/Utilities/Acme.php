<?php

namespace App\Utilities;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter as FlyLocal;
use App\Acme\Client;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Exceptions\AcmeFailedException;

/**
* Acmeチャレンジファサード処理本体
*/
class Acme
{
  /**
   * 登録されたドメインのNginx設定ファイルを作成
   * 環境変数によってNginx設定ファイルの置換文字列を設定する
   * 既にファイルが存在する場合は上書き
   * @param string $host 登録ホスト
   * @param string $host ホストに割り振るプロジェクト名
   * @param boolean $ssl http接続用(false) https接続用(true)
   * @return \Utl::makeFile()の戻り値
   */
   public static function makeNginxConf($host, $project, $ssl) {

     // 置換文字列処理
     $tmp = \File::get(config($ssl ? 'app.nginx_tpl_ssl' : 'app.nginx_tpl_nossl'));
     $conf = str_replace("%server%", $host, $tmp);
     $conf = str_replace("%root%", config('app.root_dir'), $conf);
     $conf = str_replace("%project%", $project, $conf);
     $conf = str_replace("%socket%", config('app.fpm_socket'), $conf);
     if ($ssl) {
       $conf = str_replace("%cert_dir%", config('app.acme_dir') . $host, $conf);
       $conf = str_replace("%cert_file%", config('app.acme_cert'), $conf);
       $conf = str_replace("%cert_key%", config('app.acme_key'), $conf);
       $conf = str_replace("%nginx_dir%", config('app.nginx_conf_dir'), $conf);
       $conf = str_replace("%nginx_dir%", config('app.nginx_conf_dir'), $conf);
     }
     return \Utl::makeFile(self::getNginxAvailFilePath($host), $conf);
   }

  /**
   * ドメイン有効化ディレクトリにリンクを張り、ドメインを有効にする
   * 有効化ディレクトリが存在しなければ作成する
   * @param string $host 有効化するドメイン
   * @return \Utl::makeSoftLink()の戻り値
   */
  public static function enableDomain($host) {

    $source = self::getNginxAvailFilePath($host);
    $target = self::getNginxEnableFilePath($host);
    return \Utl::makeSoftLink($source, $target);
  }

  /**
   * ドメイン有効化ディレクトリのリンクを解除し、ドメインを無効にする
   * シンボリックリンクが無ければ何もしない
   * @param string $host 無効化するドメイン
   * @return \Utl::removeSoftLink()の戻り値
   */
  public static function disableDomain($host) {

    // シンボリックリンクが無ければ何もしない
    $target = self::getNginxEnableFilePath($host);
    return \Utl::removeSoftLink($target);
  }

  /**
   * 指定ドメインのNginx設定ファイルを取得する
   * @param string $host 取得するNginx設定のドメイン
   * @return Nginx設定ファイルのコンテンツ
   */
  public static function getNginxConf($host) {
    return \File::get(self::getNginxAvailFilePath($host));
  }

  /**
   * NginxをGraceful restart(瞬断無しの再起動)
   * @param 無し
   * @return 無し
   * @throws AcmeFailedException
   */
  public static function restartNginx() {
    /*
    $process = new Process(['sudo', 'nginx', '-s', 'reload']);
    $process->run();
    $output = $process->getOutput() ?: $process->getErrorOutput();

    // プロセス実行結果確認
    if (!$process->isSuccessful()) {
      // ...
      throw new ProcessFailedException($process);
    }
    $results = shell_exec("sudo nginx -s reload");
    */
    $results = exec("sudo nginx -s reload");
    if ($results != "") {
      throw new AcmeFailedException("Nginxの再起動に失敗しました");
    }
  }

  /**
   * 登録ドメイン用Nginx設定ファイルディレクトリパス取得
   * @param 無し
   * @return string Nginx設定ファイルディレクトリパス
   */
  public static function getNginxAvailDirPath() {
    return config('app.nginx_available_dir');
  }

  /**
   * 登録ドメイン用Nginx設定ファイルのフルパス取得
   * @param 無し
   * @return string Nginx設定ファイルのフルパス
   */
  public static function getNginxAvailFilePath($host) {
    return self::getNginxAvailDirPath(). $host;
  }

  /**
   * 登録ドメイン用Nginx設定有効化ディレクトリパス取得
   * @param 無し
   * @return string 有効化ディレクトリパス
   */
  public static function getNginxEnablelDirPath() {
    return config('app.nginx_enabled_dir');
  }

  /**
   * 登録ドメイン用Nginx設定有効化ファイルのフルパス取得
   * @param 無し
   * @return string 有有効化ファイルのフルパス
   */
  public static function getNginxEnableFilePath($host) {
    return self::getNginxEnablelDirPath(). $host;
  }

  /**
   * 各ドメイン用acme関連ファイル格納ディレクトリパス取得
   * @param string $domain 取得するacmeディレクトリパスの適用ドメイン
   * @return string 指定ドメインのacme関連ファイル格納ディレクトリパス
   */
  public static function getAcmeDirPath($domain) {
    return config('app.acme_dir') . $domain;
  }

  /**
   * 各ドメイン用acme関連ファイルフルパス取得
   * @param string $domain 取得するacmeディレクトリパスの適用ドメイン
   * @param string $fname ファイル名
   * @return string 指定ドメイン：指定ファイルのacme関連ファイルのフルパス
   */
  public static function getAcmeFilePath($domain, $fname) {
    return self::getAcmeDirPath($domain) . '/' . $fname;
  }

  /**
   * 各ドメイン用Acme関連ファイル作成
   * @param string $domain acmeディレクトリパスの適用ドメイン
   * @param string $fname ファイル名
   * @param string $contents ファイルコンテンツ
   * @return \Utl::makeFile()の戻り値
   */
  public static function putAcmefile($domain, $fname, $contents) {
    $path = self::getAcmeFilePath($domain, $fname);
    return \Utl::makeFile($path, $contents);
  }

  /**
   * Acme関連ファイル取得
   * @param string $domain 取得するacmeディレクトリパスの適用ドメイン
   * @param string $fname ファイル名
   * @return string 指定ドメイン：指定ファイルのacme関連ファイルのコンテンツ
   */
  public static function getAcmeFile($domain, $fname) {
    $res = \Utl::getFile(self::getAcmeFilePath($domain, $fname));
    return $res;
  }

  /**
   * YaacによるAcmeチャレンジ
   * Yaacを利用して鍵と証明書を取得し、nginx設定ファイルとSSL関連ファイルを新規作成する
   * Nginxは処理完了後再起動済みで、そのまま登録ドメインによる画面表示が可能となる
   * @param string $domain 証明書を崇徳するドメイン
   * @return 無し
   * @throws AcmeFailedException
   */
  public static function doChallenge($domain) {

    //Prepare flysystem
    $adapter = new FlyLocal(config('app.acme_dir'));
    $filesystem = new Filesystem($adapter);

    //Construct the client
    $client = new Client([
      'username' => config('app.acme_mail'),
      'fs'       => $filesystem,
      //'mode'     => Client::MODE_STAGING,
      'mode'     => Client::MODE_LIVE,
    ]);
    $order = $client->createOrder([$domain]);
    $authorizations = $client->authorize($order);

    // HTTP validation
    $authorization = $file = null;
    foreach ($authorizations as $authorization) {

      // チャレンジ用トークン(yaacで最初に作成されるファイル)作成
      $file = $authorization->getFile();
      self::putAcmeFile($domain, $file->getFilename(), $file->getContents());

      // Self Test
      if (!$client->selfTest($authorization, Client::VALIDATION_HTTP)) {
        throw new AcmeFailedException('ドメインの名前解決が正しくありません');
      }

      // HTTP validation:
      $client->validate($authorization->getHttpChallenge(), 15);
    }

    // Clientの状態を確認(HTTPチャレンジのみがready状態なので、これのみ実施される)
    if (!$client->isReady($order)) {
      throw new AcmeFailedException('SSL証明書取得用クライアントが正常に作成できませんでした');
    }

    $certificate = $client->getCertificate($order);
    // 証明書ファイル作成
    self::putAcmeFile($domain, config('app.acme_cert'), $certificate->getCertificate());

    // プライベートキー作成
    self::putAcmeFile($domain, config('app.acme_key'), $certificate->getPrivateKey());
  }
}
