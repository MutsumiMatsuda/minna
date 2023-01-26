<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as HttpClient;

/**
* yaacによるacmeチャレンジ実行コントローラー
*/
class AcmeController extends Controller
{

  /**
  * ドメイン登録画面を表示
  */
  public function add(Request $req) {
    //dd(\Acme::getNginxTemplate());
    return view("domain.add");
  }

  // 送信されたドメイン用のSSL証明書とキーを生成し、ファイルとDBに保存
  public function create(Request $req) {

    $domain = $req->domain;

    // Nginx設定ファイル(http)作成
    \Acme::makeNginxConf($domain, 'minna', false);

    // ドメイン(http)有効化
    \Acme::enableDomain($domain);

    // Nginx再起動
    \Acme::restartNginx();

    // SSL申請
    \Acme::doChallenge($domain);

    // Nginx設定ファイル(SSL)作成
    \Acme::makeNginxConf($domain, 'minna', true);

    // Nginx再起動
    \Acme::restartNginx();

    // 登録済みドメインにSSLでリダイレクト
    return redirect()->away('https://' . $domain);
  }

  /**
   * Yaacのセルフテスト用api
   * 照合用のトークン文字列を返す
   * @param Request リクエスト
   * @return
   */
  public function challenge(Request $req) {
    dd($req->getHost());
    return \Acme::getAcmeFile($req->getHost(), $req->fname);
  }

  /**
   * 登録済みのドメインを無効化
   */
  public function remove(Request $req) {

  }

  public function test() {
    $c = new HttpClient([
        'verify'          => false,
        'timeout'         => 10,
        'connect_timeout' => 3,
        //'timeout'         => 30,
        //'connect_timeout' => 30,
        'proxy' => 'http://18.210.158.25:3128',
        'allow_redirects' => true,
    ]);
    //$res = $c->request('GET', 'http://beautychat.net/.well-known/acme-challenge/wa_OqGitCkT-DTS6bY5kZs7zk94Pe4uxYJwCAozUryE');
    //$res = $c->request('GET', 'http://recipes.giize.com/test');
    $res = $c->request('GET', 'http://beautychat.net/api');
    dd((string)$res->getBody());
  }

  public function api(Request $req) {
    $json = [
      'status' => 200,
      'data' => [
        'this is response!'
      ]
    ];
    //return $json_encode($json);
    return "this is beautychat.net!";
  }
}
