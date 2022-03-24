<?php
class Download
{

    public function __construct()
    {
        Auth::user(0, 1);
    }

    public function index()
    {
        // Check The User
        if ($_SESSION['loggedin']) {
            if (Users::get("can_download") == "no") {
                Redirect::autolink(URLROOT, Lang::T("NO_PERMISSION_TO_DOWNLOAD"));
            }
            if (Users::get("downloadbanned") == "yes") {
                Redirect::autolink(URLROOT, Lang::T("DOWNLOADBAN"));
            }
        }

        // Get The Id
        $id = (int) Input::get("id");

        if (!$id) {
            Redirect::autolink(URLROOT, Lang::T("ID_NOT_FOUND_MSG_DL"));
        }
        
        // Check The Torrent
        $row = DB::select('torrents', 'filename, banned, external, announce, owner, vip', ['id'=>intval($id)]);
        if (Users::get('class') < _VIP && $row['vip'] == "yes") {
            Redirect::autolink($_SERVER['HTTP_REFERER'], Lang::T("VIPTODOWNLOAD"));
        }
        if (!$row) {
            Redirect::autolink(URLROOT . '/home', Lang::T("ID_NOT_FOUND"));
        }
        if ($row["banned"] == "yes") {
            Redirect::autolink($_SERVER['HTTP_REFERER'], Lang::T("BANNED_TORRENT"));
        }

        // Thanks
        if (Config::get('FORCETHANKS') && $_SESSION['loggedin']) {
            if (Users::get("id") != $row["owner"]) {
                $like = DB::select('thanks', 'user', ['thanked'=>$id, 'type'=>'torrent', 'user'=>Users::get('id')]);
                if (!$like) {
                    Redirect::autolink($_SERVER['HTTP_REFERER'], Lang::T("PLEASE_THANK"));
                }
            }
        }

        // Check The File
        $fn = UPLOADDIR . "/torrents/$id.torrent";
        if (!is_file($fn)) {
            Redirect::autolink($_SERVER['HTTP_REFERER'], Lang::T("FILE_NOT_FILE"));
        }
        if (!is_readable($fn)) {
            Redirect::autolink($_SERVER['HTTP_REFERER'], Lang::T("FILE_UNREADABLE"));
        }

        // Name Download File
        $name = $row['filename'];
        $friendlyurl = str_replace("http://", "", URLROOT);
        $friendlyname = str_replace(".torrent", "", $name);
        $friendlyext = ".torrent";
        $name = $friendlyname . "[" . $friendlyurl . "]" . $friendlyext;
        

        // Update Hit When Downloaded
        $pvkey_var = "d_".$id;
        $views = $_SESSION[$pvkey_var] == 1 ? $row["hits"] + 0 : $row["hits"] + 1;
        $_SESSION[$pvkey_var] = 1;
        DB::update('torrents', ['hits'=>$views], ['id'=>$id]);
		
        // if user dont have a passkey generate one, only if current member
        if ($_SESSION['loggedin']) {
            if (strlen(Users::get('passkey')) != 32) {
                $rand = array_sum(explode(" ", microtime()));
                $passkey = md5(Users::get('username') . $rand . Users::get('secret') . ($rand * mt_rand()));
                DB::update('users', ['passkey'=>$passkey], ['id'=>Users::get('id')]);
            }
        }
        
        // Check passkey
        $passkey = $_GET["passkey"] ? $_GET["passkey"] : Users::get('passkey');

        // Local Torrent To Add Passkey
        if ($row["external"] != 'yes') {
            // Bencode
            $dict = Bencode::decode(file_get_contents($fn));
            $dict['announce'] = sprintf(PASSKEYURL, $passkey);
            unset($dict['announce-list']);
            $data = Bencode::encode($dict);
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header("Content-Type: application/x-bittorrent");
            print $data;
        } else {
            // Download External
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . filesize($fn));
            header("Content-Type: application/x-bittorrent");
            readfile($fn);
        }
    }

    public function attachment()
    {
        $id = (int) Input::get("id");
        $filename = Input::get("hash");

        $fn = UPLOADDIR . "/attachment/$filename.data";
        $sql = DB::select('attachments', '*', ['id'=>$id]);
        $extension = substr($sql['filename'], -3);
        
        if (!file_exists($fn)) {
            Redirect::autolink($_SERVER['HTTP_REFERER'], "The file $filename does not exists");
        } else {
            header('Content-Disposition: attachment; filename="' . $sql['filename'] . '"');
            header('Content-Length: ' . filesize($fn));
            header("Content-Type: application/$extension");
            readfile($fn);
        }
    }

    public function images()
    {
        $file_hash = Input::get("hash");

        $switchimage = UPLOADDIR . "/attachment/$file_hash.data";
        if (file_exists($switchimage)) {
            //list($width, $height) = getimagesize($plik);
            ?> 
            <img alt="test image" src="<?php echo data_uri($switchimage, $file_hash); ?>"> <?php
        }
    }

}