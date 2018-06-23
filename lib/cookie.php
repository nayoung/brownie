<?php

/*
 * @PATH           : 
 * @Description    : 
 * @Author         : 
 * @since Date     : 
 * @history        : 
 */

class Cookie
{
    const nExpireTime = 0; // expire 타임
    const sCryptCipher = MCRYPT_BLOWFISH; // mcrypt 알고리즘
    const sCryptMode = MCRYPT_MODE_CBC; // mcrypt 모드
    const nCryptIV = 81628100; // mcrypt 알고리즘 block size

    public $sCookieName;
    public $sCookieValueName;
    public $bCryptApply; // 암호화 여부
    public $nExpTime;

    private $sCryptKey;
    private $aCookieValues;
    private $sCookieDomain;

    function __construct($sName = NULL)
    {
        $this->sCookieName = $sName;
        $this->aCookieValues = array();

        $this->sCookieDomain = "";
        $this->sCookieValueName = "";
        $this->nExpTime = self::nExpireTime;

        $this->sCryptKey = self::get_remote_addr(); // 암호화를 위한 키값이 없을 경우 임의로 유저의 아이피로 생성
    }

    function __destruct()
    {
        unset($this->aCookieValues);
    }

    public function __get($sValueName)
    {
        $sCookieValue = $this->aCookieValues[$sValueName];
        return ($sCookieValue);
    }

    public function __set($sValueName, $sValue)
    {
        $this->aCookieValues[$sValueName] = $sValue;
    }

    /*
     * 사용자 아이피 받아오는 함수
     */
    private function get_remote_addr()
    {
        if (!$sUserIp = getenv('HTTP_CLIENT_IP')) {
            if (!$sUserIp = getenv('HTTP_X_FORWARDED_FOR')) {
                $sUserIp = getenv('REMOTE_ADDR');
            }
        }
        $aUserIp = explode(',', $sUserIp);

        //-- 모바일 브라우져일경우 
        if (preg_match("/Mobile/i", $_SERVER['HTTP_USER_AGENT']) == true) {
            $aUserIp[0] = '0.00.000.0';
        }

        return trim($aUserIp[0]);
    }

    /*
     * mcrypt 암호화 함수
     */
    public function encrypt_cookie($aValues)
    {
        $sHandleCrypt = mcrypt_module_open(self::sCryptCipher, "", self::sCryptMode, ""); // mcrypt 모듈에 사용할 알고리즘과 모드 선언 및 열기
        $nKeySize = mcrypt_enc_get_key_size($sHandleCrypt); // 모드에 대한 최대 키사이즈
        $sKey = substr(md5($this->sCryptKey), 0, $nKeySize);
        mcrypt_generic_init($sHandleCrypt, $sKey, self::nCryptIV); // mcrypt 암호화를 위한 초기화 함수

        foreach ($aValues as $sValueName => $sValue) {
            $aEncryptedValues[$sValueName] = trim(base64_encode(mcrypt_generic($sHandleCrypt, $sValue))); // 암호화 데이터
        }

        mcrypt_generic_deinit($sHandleCrypt); // mcrypt 암호화 종료
        mcrypt_module_close($sHandleCrypt); // mcrypt 모듈 닫기

        unset($sHandleCrypt, $nKeySize, $sKey);

        return ($aEncryptedValues);
    }

    /*
     * mcrypt 복호화 함수
     */
    public function decrypt_cookie($aValues)
    {
        $sHandleCrypt = mcrypt_module_open(self::sCryptCipher, "", self::sCryptMode, "");
        $nKeySize = mcrypt_enc_get_key_size($sHandleCrypt);
        $sKey = substr(md5($this->sCryptKey), 0, $nKeySize);
        mcrypt_generic_init($sHandleCrypt, $sKey, self::nCryptIV);

        foreach ($aValues as $sValueName => $sEncryptedValue) {
            $aDecryptedValues[$sValueName] = trim(mdecrypt_generic($sHandleCrypt, base64_decode($sEncryptedValue)));
        }

        mcrypt_generic_deinit($sHandleCrypt);
        mcrypt_module_close($sHandleCrypt);

        unset($sHandleCrypt, $nKeySize, $sKey);

        return ($aDecryptedValues);
    }

    /*
     * 쿠키 초기화 함수
     */
    public function init_cookie($sDomain = '', $bCrypt = true)
    {
        $this->sCookieDomain = $sDomain;
        $this->bCryptApply = $bCrypt;

        if (isset($_COOKIE[$this->sCookieName]) || isset($_COOKIE[$this->sCookieValueName])) {
            if (isset($_COOKIE[$this->sCookieName])) {
                $aCookie = $_COOKIE[$this->sCookieName];
            } else {
                $aCookie[$this->sCookieValueName] = $_COOKIE[$this->sCookieValueName];
            }

            if ($this->bCryptApply) {
                $this->aCookieValues = $this->decrypt_cookie($aCookie);
            } else {
                $this->aCookieValues = $aCookie;
            }

            unset($aCookie);
        }
    }

    /*
     * 암호화 키 생성 함수
     */
    public function set_key($sCryptKey)
    {
        $this->sCryptKey = $sCryptKey;
        $this->bCryptApply = true;
    }

    /*
     * 쿠키 생성 함수
     */
    public function set_cookie()
    {
        $nSetExpTime = $this->nExpTime;
        if ($this->nExpTime > 0) {
            $nSetExpTime = time() + $this->nExpTime;
        }

        if (count($this->aCookieValues) > 0) {
            $aCookieData = ($this->bCryptApply) ? $this->encrypt_cookie($this->aCookieValues) : $this->aCookieValues;
            foreach ($aCookieData as $sValueName => $sValue) {
                setcookie(self::get_cookie_name($sValueName), $sValue, $nSetExpTime, "/", $this->sCookieDomain);
            }
            unset($aCookieData);
        }
    }

    /*
     * 쿠키 이름 찾기 함수
     */
    private function get_cookie_name($sValueName)
    {
        if ($this->sCookieName) {
            $sValueName = $this->sCookieName . "[" . $sValueName . "]";
        }
        return $sValueName;
    }

    /*
     * 쿠키 삭제 함수
     */
    public function destroy_cookie()
    {
        foreach ($this->aCookieValues as $sValueName => $sValue) {
            unset($this->aCookieValues[$sValueName]);
            setcookie(self::get_cookie_name($sValueName), '', -0, "/", $this->sCookieDomain);
        }
    }

    /*
     * 쿠키 삭제 함수
     */
    public function remove_cookie($sValueName)
    {
        if (isset($this->aCookieValues[$sValueName])) {
            unset($this->aCookieValues[$sValueName]);
        }
        setcookie(self::get_cookie_name($sValueName), "", -0, "/", $this->sCookieDomain);
    }
}