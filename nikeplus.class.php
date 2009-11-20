<?php
// NikePlus API
// Rasmus Lerdorf January 2007
// Requires PHP 5 with SimpleXML and Curl
class NikePlus {
#    const auth_url = 'https://www.nike.com/nikeplus/v1/services/widget/generate_pin.jhtml';
# http://nikerunning.nike.com/nikeplus/v1/services/widget/get_public_run_list.jsp?userID=887557392
    const auth_url = 'https://secure-nikeplus.nike.com/services/profileService';
    const data_url = 'https://secure-nikerunning.nike.com/nikeplus/v1/services/app/get_user_data.jsp';
    const list_url = 'https://secure-nikerunning.nike.com/nikeplus/v1/services/app/run_list.jsp';
    const run_url  = 'https://secure-nikerunning.nike.com/nikeplus/v1/services/app/get_run.jsp';
    const goal_url = 'https://secure-nikerunning.nike.com/nikeplus/v1/services/app/goal_list.jsp';
    const chal_url = 'https://secure-nikerunning.nike.com/nikeplus/v1/services/widget/get_challenges_for_user.jsp';
    const chal_detail_url = 'https://secure-nikerunning.nike.com/nikeplus/v1/services/app/get_challenge_detail.jsp';

    private $login, $password, $cache_path, $ttl, $cookiejar;
    public  $runs, $data, $goals, $challenges, $pin;

    private function auth() {
        $ch = curl_init();
        $this->cookiejar = $this->cache_path.'/nikeplus_'.$this->login.'_cookies';
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $this->cookiejar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
        // Refresh the cookiejar every now and then
        if(!file_exists($this->cookiejar) || filemtime($this->cookiejar) < time()-7200) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, NikePlus::auth_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=login&login='.htmlentities($this->login).'&password='.htmlentities($this->password).'&locale=en%5FUS');

            $auth_xml = curl_exec($ch);
            if($auth_xml) { 
                $auth_xml = simplexml_load_string($auth_xml);
                $this->status = (string)$auth_xml->status;
                // What's the pin for?
                $this->pin = (string)$auth_xml->pin;
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_POST, 0);
        return $ch;
    }

    function __construct($login, $password, $cache_path='/var/tmp', $ttl = 21600) {
        $this->login = $login;
        $this->password = $password;
        $this->cache_path = $cache_path;
        $this->ttl = $ttl;
        if(!is_writable($cache_path)) throw new Exception('Cache directory '.$cache_path.' is not wriable');

        $ch = $this->auth();

        $listfile = $this->cache_path.'/nikeplus_'.$this->login.'_runs.xml';
        if(!file_exists($listfile) || filemtime($listfile) < time()-$this->ttl) {
            curl_setopt($ch, CURLOPT_FILE, $fp = fopen($listfile,'w'));
            curl_setopt($ch, CURLOPT_URL, NikePlus::list_url);
            curl_exec($ch); fclose($fp);
        }
        $this->runs = simplexml_load_file($listfile);

        $datafile = $this->cache_path.'/nikeplus_'.$this->login.'_data.xml';
        if(!file_exists($datafile) || filemtime($datafile) < time()-$this->ttl) {
            curl_setopt($ch, CURLOPT_FILE, $fp = fopen($datafile,'w'));
            curl_setopt($ch, CURLOPT_URL, NikePlus::data_url);
            curl_exec($ch); fclose($fp);
        }
        $this->data = simplexml_load_file($datafile);

        $goalfile = $this->cache_path.'/nikeplus_'.$this->login.'_goals.xml';
        if(!file_exists($goalfile) || filemtime($goalfile) < time()-$this->ttl) {
            curl_setopt($ch, CURLOPT_FILE, $fp = fopen($goalfile,'w'));
            curl_setopt($ch, CURLOPT_URL, NikePlus::goal_url);
            curl_exec($ch); fclose($fp);
        }
        $this->goals = simplexml_load_file($goalfile);

        $chalfile = $this->cache_path.'/nikeplus_'.$this->login.'_challenges.xml';
        if(!file_exists($chalfile) || filemtime($chalfile) < time()-$this->ttl) {
            curl_setopt($ch, CURLOPT_FILE, $fp = fopen($chalfile,'w'));
            curl_setopt($ch, CURLOPT_URL, NikePlus::chal_url);
            curl_exec($ch); fclose($fp);
        }
        $this->challenges = simplexml_load_file($chalfile);
        curl_close($ch);
    } 

    function run($id) {
        $ch = $this->auth();
        $runfile = $this->cache_path.'/nikeplus_'.$this->login.'_'.$id.'.xml';
        if(!file_exists($runfile)) {
            curl_setopt($ch, CURLOPT_FILE, $fp = fopen($runfile,'w'));
            curl_setopt($ch, CURLOPT_URL, NikePlus::run_url.'?id='.$id);
            curl_exec($ch); fclose($fp);
        }
        $err = curl_error($ch);
        curl_close($ch);
        if(filesize($runfile)) return simplexml_load_file($runfile);
        else return $err;
    }

    function challenge($id) {
        $ch = $this->auth();
        $chalfile = $this->cache_path.'/nikeplus_'.$this->login.'_chal_'.$id.'.xml';
        if(!file_exists($chalfile) || filemtime($chalfile) < time()-1800) {
            curl_setopt($ch, CURLOPT_FILE, $fp = fopen($chalfile,'w'));
            curl_setopt($ch, CURLOPT_URL, NikePlus::chal_detail_url.'?id='.$id);
            curl_exec($ch); fclose($fp);
        }
        $err = curl_error($ch);
        curl_close($ch);
        if(filesize($chalfile)) return @simplexml_load_file($chalfile);
        else return $err;
    }

    static function duration($dur) {
        $hours   = (int)($dur/3600000);
        $minutes = (int)(($dur%3600000)/60000);
        $seconds = (int)((($dur%3600000)%60000)/1000);
        if($hours) return sprintf("%2d:%02d'%02d\"", $hours, $minutes, $seconds);
        else return sprintf("%2d'%02d\"", $minutes, $seconds);
    }

    static function pace($dist,$dur,$unit="mi") {
        if(!$dist) return "";
        if($unit=="mi") $dist = (double)$dist/1.609344;
        else $dist = (double)$dist;
        $pace = @(($dur/1000) / $dist);
        $min = (int)($pace/60); 
        $sec = $pace%60 ;
        return sprintf("%2d'%02d\"", $min, $sec);
    }
}

?>