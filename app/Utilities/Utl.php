<?php

namespace App\Utilities;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * ユーティリティファサード
 * ディレクトリ、ファイル、シンボリックリンク操作の各機能を実装
 */
class Utl {

  /**
   * 指定ディレクトリが存在しなければ再帰的に作成
   * @param string @path 作成するディレクトリのフルパス
   * @param string $permission (デフォルト0755)
   * @return File::makeDirectoryの戻り値　指定ディレクトリが既存の場合true
   */
  public static function makeDir($path, $permisson='0755') {

    $ret = true;
    if (\File::missing($path)) {
      $ret = \File::makeDirectory($path, $permisson, true);
    }
    return $ret;
  }

  /**
   * 指定ファイルを作成する
   * 指定ファイルの格納ディレクトリが存在しなければ再帰的に作成する
   * ファイルが既存ならば一旦削除して再作成
   * @param string $path ファイルのフルパス
   * @param string $contents ファイルのコンテンツ
   * @param string $permission ファイルのパーミッション(デフォルト0755)
   * @return \File::put の戻り値
   */
  public static function makeFile($path, $contents, $permisson='0755') {

    $ret = true;
    // ファイルが存在すれば削除
    if (\File::exists($path)) {
      $ret = \File::delete($path);
    }

    if($ret) {
      // ディレクトリが存在しなければ作成
      $dir = pathinfo($path, PATHINFO_DIRNAME);
      $ret = self::makeDir($dir);
    }

    if ($ret) {
      // ファイル作成
      $ret = \File::put($path, $contents, $permisson, true);
    }
    return $ret;
  }

  /**
   * 指定ファイルを取得する
   * ファイルが存在しなければ空文字を返す
   * @param string $path ファイルのフルパス
   * @return ファイルのコンテンツ(ファイルが存在しなければ空文字)
   */
   public static function getFile($path) {
     $ret = '';
     if (\File::exists($path)) {
       $ret = \File::get($path);
     }
     return $ret;
   }

  /**
   * ソフトシンボリックリンクを作成する
   * リンク格納用ディレクトリが存在しなければ再帰的に作成する
   * 既存ならば何もしない
   * @param string $source ソフトリンクのソース(フルパス)
   * @param string $target 作成するソフトリンク(フルパス)
   * @return 成功時true 失敗時false
   */
  public static function makeSoftLink($source, $target) {

    // リンク格納用ディレクトリが存在しなければ作成
    $dir = pathinfo($target, PATHINFO_DIRNAME);
    $ret = self::makeDir($dir);

    if ($ret && \File::missing($target)) {
      $process = new Process(['ln', '-s', $source, $target]);
      $process->run();
      $output = $process->getOutput() ?: $process->getErrorOutput();

      // プロセス実行結果確認
      if (!$process->isSuccessful()) {
         // throw new ProcessFailedException($process);
         $ret = false;
      }
    }
    return $ret;
  }

  /**
   * ソフトシンボリックリンクを解除する
   * リンクが無ければ何もしない
   * @param string $target 解除するソフトリンク(フルパス)
   * @return 成功時true 失敗時false
   */
  public static function removeSoftLink($target) {

    $ret = true;
    if (\File::exists($target)) {
      $process = new Process(['unlink', $target]);
      $process->run();
      $output = $process->getOutput() ?: $process->getErrorOutput();

      // プロセス実行結果確認
      if (!$process->isSuccessful()) {
        // throw new ProcessFailedException($process);
        $ret = false;
      }
    }
    return $ret;
  }
}
