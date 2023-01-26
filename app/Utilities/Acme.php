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
   * 既にファイルが存在する場合は上書き
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
     //dd($host, self::getNginxAvailFilePath($host));
     \Utl::makeFile(self::getNginxAvailFilePath($host), $conf);
   }

  /**
   * ドメイン有効化ディレクトリにリンクを張り、ドメインを有効にする
   * 有効化ディレクトリが存在しなければ作成する
   */
  public static function enableDomain($host) {

    $source = self::getNginxAvailFilePath($host);
    $target = self::getNginxEnableFilePath($host);
    return \Utl::makeSoftLink($source, $target);
  }

  /**
   * ドメイン有効化ディレクトリのリンクを解除し、ドメインを無効にする
   * シンボリックリンクが無ければ何もしない
   */
  public static function disableDomain($host) {

    // シンボリックリンクが無ければ何もしない
    $target = self::getNginxEnableFilePath($host);
    return \Utl::removeSoftLink($target);
  }

  /**
   * 指定ドメインのNginx設定ファイルを取得する
   */
  public static function getNginxConf($host) {
    return \File::get(self::getNginxAvailFilePath($host));
  }

  /**
   * NginxをGraceful restart(瞬断無しの再起動)
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
    */
    $results = shell_exec("sudo nginx -s reload");
    if ($results != "") {
      throw new AcmeFailedException("Nginxの再起動に失敗しました");
    }
  }

  /**
   * 登録ドメイン用Nginx設定ファイルディレクトリパス取得
   */
  public static function getNginxAvailDirPath() {
    return config('app.nginx_available_dir');
  }

  /**
   * 登録ドメイン用Nginx設定ファイルのフルパス取得
   */
  public static function getNginxAvailFilePath($host) {
    return self::getNginxAvailDirPath(). $host;
  }

  /**
   * 登録ドメイン用Nginx設定有効化ディレクトリパス取得
   */
  public static function getNginxEnablelDirPath() {
    return config('app.nginx_enabled_dir');
  }

  /**
   * 登録ドメイン用Nginx設定有効化ファイルのフルパス取得
   */
  public static function getNginxEnableFilePath($host) {
    return self::getNginxEnablelDirPath(). $host;
  }

  /**
   * 各ドメイン用acme関連ファイル格納ディレクトリパス取得
   */
  public static function getAcmeDirPath($domain) {
    return config('app.acme_dir') . $domain;
  }

  /**
   * 各ドメイン用acme関連ファイルフルパス取得
   */
  public static function getAcmeFilePath($domain, $fname) {
    return self::getAcmeDirPath($domain) . '/' . $fname;
  }

  /**
   * Acme関連ファイル作成
   */
  public static function putAcmefile($domain, $fname, $contents) {
    $path = self::getAcmeFilePath($domain, $fname);
    return \Utl::makeFile($path, $contents);
  }

  /**
   * Acme関連ファイル取得
   */
  public static function getAcmeFile($domain, $fname) {
    $res = \Utl::getFile(self::getAcmeFilePath($domain, $fname));
    return $res;
  }

  /**
   * YaacによるAcmeチャレンジ
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
